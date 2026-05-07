import os
from dotenv import load_dotenv

load_dotenv()

os.environ.setdefault("QT_QPA_PLATFORM", os.getenv("QT_QPA_PLATFORM", "xcb"))

import cv2
import face_recognition
import logging
import pickle
import numpy as np
import requests
import time
import threading
from datetime import datetime
from logging.handlers import TimedRotatingFileHandler

from config import (
    API_BASE_URL,
    API_KEY,
    CAMERA_SOURCE,
    TOLERANCE,
    PROCESS_EVERY_N_FRAMES,
    MARK_COOLDOWN_SECONDS,
    ENCODE_POLL_INTERVAL,
)
from encoder import encode_student, mark_encoded

os.makedirs("logs", exist_ok=True)

log = logging.getLogger("smartattend")
log.setLevel(logging.DEBUG)

_fmt = logging.Formatter(
    "%(asctime)s  %(levelname)-8s  %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)

# Active log:   logs/recognize.2026-05-07.log
# Rotated logs: logs/recognize.2026-05-06.log  (previous days)
_today = datetime.now().strftime("%Y-%m-%d")
_fh = TimedRotatingFileHandler(
    f"logs/recognize.{_today}.log",
    when="midnight",
    backupCount=30,
    encoding="utf-8",
)

def _log_namer(default_name: str) -> str:
    """Rename  recognize.2026-05-07.log.2026-05-08  →  recognize.2026-05-08.log"""
    if ".log." in default_name:
        base, date = default_name.rsplit(".log.", 1)
        return f"{base}.{date}.log"
    return default_name

_fh.namer = _log_namer
_fh.setFormatter(_fmt)
log.addHandler(_fh)

_ch = logging.StreamHandler()
_ch.setFormatter(_fmt)
log.addHandler(_ch)

marked_today   = {}   # { "STU-001": "2025-04-17" }
notified_today = set()
last_attempt   = {}   # { "STU-001": <unix timestamp> } — throttle ALL attempts

def already_marked_today(student_id: str) -> bool:
    today = datetime.now().strftime("%Y-%m-%d")
    return marked_today.get(student_id) == today

def record_marked(student_id: str):
    today = datetime.now().strftime("%Y-%m-%d")
    marked_today[student_id] = today

def cooldown_ok(student_id: str) -> bool:
    """Returns True if enough time has passed since the last attempt (success or failure)."""
    now  = time.time()
    last = last_attempt.get(student_id, 0)
    if now - last >= MARK_COOLDOWN_SECONDS:
        last_attempt[student_id] = now
        return True
    return False

log.info("Loading face encodings...")
try:
    with open("encodings/encodings.pkl", "rb") as f:
        data = pickle.load(f)
    known_encodings  = data["encodings"]
    known_identities = data["identities"]
    log.info("Loaded %d known face(s)", len(known_identities))
except FileNotFoundError:
    known_encodings  = []
    known_identities = []
    log.warning("No encodings.pkl found — starting with empty model. The polling thread will encode students automatically.")

# Protects known_encodings / known_identities during hot-swap
_model_lock = threading.Lock()

# Serialises ALL dlib/face_recognition calls — dlib is not thread-safe
_dlib_lock = threading.Lock()


def _save_encodings() -> None:
    """Persist the current in-memory model to encodings.pkl so restarts are instant."""
    os.makedirs("encodings", exist_ok=True)
    with _model_lock:
        data = {"encodings": list(known_encodings), "identities": list(known_identities)}
    try:
        with open("encodings/encodings.pkl", "wb") as f:
            pickle.dump(data, f)
        log.info("Encodings saved to disk (%d total).", len(data["encodings"]))
    except Exception as e:
        log.warning("Could not save encodings.pkl: %s", e)


def _poll_and_encode() -> None:
    """
    Background thread: every ENCODE_POLL_INTERVAL seconds, fetch students
    with face_encoded=false, encode them, and hot-swap them into the live model.
    """
    while True:
        time.sleep(ENCODE_POLL_INTERVAL)
        try:
            resp = requests.get(
                f"{API_BASE_URL}/students/for-encoding",
                params={"unencoded": 1},
                headers={"X-API-Key": API_KEY},
                timeout=15,
            )
            resp.raise_for_status()
            students = resp.json().get("students", [])
        except Exception as e:
            log.warning("Encode poll failed: %s", e)
            continue

        if not students:
            continue

        log.info("Encode poll: %d student(s) need encoding.", len(students))

        any_encoded = False
        for student in students:
            pairs = encode_student(student, _dlib_lock)
            if not pairs:
                log.warning("No encodings generated for %s — skipping.", student["name"])
                continue

            sid = student["student_id"]
            with _model_lock:
                combined = [
                    (e, i) for e, i in zip(known_encodings, known_identities)
                    if i["student_id"] != sid
                ]
                known_encodings[:] = [c[0] for c in combined]
                known_identities[:] = [c[1] for c in combined]
                for enc, ident in pairs:
                    known_encodings.append(enc)
                    known_identities.append(ident)

            log.info("Hot-loaded %d encoding(s) for %s.", len(pairs), student["name"])
            mark_encoded(student["id"], student["name"])
            any_encoded = True

        if any_encoded:
            _save_encodings()

threading.Thread(target=_poll_and_encode, daemon=True, name="encode-poller").start()
log.info("Encode poller started (interval: %ds).", ENCODE_POLL_INTERVAL)

def mark_attendance(student_id: str, full_name: str):
    """Send attendance record to Laravel API (runs in background thread)."""
    try:
        response = requests.post(
            f"{API_BASE_URL}/attendance",
            json={"student_id": student_id},
            headers={"X-API-Key": API_KEY},
            timeout=5,
        )

        if response.status_code == 200:
            data = response.json()

            if data.get("already_marked"):
                record_marked(student_id)
                if student_id not in notified_today:
                    log.info("%s (%s) already marked earlier today.", full_name, student_id)
                    notified_today.add(student_id)
                return

            class_name = data.get("class", "N/A")
            log.info("MARKED  %s (%s) | Class: %s", full_name, student_id, class_name)
            record_marked(student_id)

        elif response.status_code == 404:
            log.warning("Student %s not found in Laravel database.", student_id)

        else:
            log.error("API returned %d for %s", response.status_code, student_id)

    except requests.exceptions.ConnectionError:
        log.warning("Cannot reach API — is Laravel running? Skipping %s", student_id)
    except requests.exceptions.Timeout:
        log.warning("API timeout for %s", student_id)

def open_camera(primary_source):
    sources = (
        [primary_source] if not isinstance(primary_source, int)
        else [primary_source] + [i for i in range(3) if i != primary_source]
    )
    for src in sources:
        log.info("Trying camera: %s", src)
        cam = cv2.VideoCapture(src)
        if cam.isOpened():
            log.info("Opened camera: %s", src)
            return cam
        cam.release()
    return None

cap = open_camera(CAMERA_SOURCE)

if cap is None:
    log.error(
        "Could not open any camera (tried %s and fallbacks). Check: ls /dev/video*",
        CAMERA_SOURCE,
    )
    exit(1)

cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)

log.info("Recognition running. Press Q to quit.")

frame_count = 0
today_str   = datetime.now().strftime("%Y-%m-%d")

while True:
    try:
        if cap is None:
            break
        ret, frame = cap.read()
        if not ret:
            log.warning("Frame grab failed, attempting reconnect...")
            for attempt in range(1, 6):
                time.sleep(1)
                if cap is not None:
                    cap.release()
                cap = open_camera(CAMERA_SOURCE)
                if cap is not None:
                    ret, frame = cap.read()
                    if ret:
                        log.info("Reconnected on attempt %d.", attempt)
                        break
            else:
                log.error("Could not reconnect to camera. Exiting.")
                break

        current_day = datetime.now().strftime("%Y-%m-%d")
        if current_day != today_str:
            log.info("New day detected (%s). Resetting attendance tracking.", current_day)
            marked_today.clear()
            notified_today.clear()
            today_str = current_day

        frame_count += 1

        if frame_count % PROCESS_EVERY_N_FRAMES != 0:
            cv2.imshow("SmartAttend — Face Recognition", frame)
            if cv2.waitKey(1) & 0xFF == ord('q'):
                break
            continue

        small = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
        rgb   = cv2.cvtColor(small, cv2.COLOR_BGR2RGB)

        with _dlib_lock:
            locations = face_recognition.face_locations(rgb)
            encodings = face_recognition.face_encodings(rgb, locations)

        # Snapshot the model so the encode-poller thread can't mid-swap during a frame
        with _model_lock:
            enc_snap = list(known_encodings)
            id_snap  = list(known_identities)

        for encoding, location in zip(encodings, locations):
            distances = face_recognition.face_distance(enc_snap, encoding)
            matches   = face_recognition.compare_faces(enc_snap, encoding, tolerance=TOLERANCE)

            name       = "Unknown"
            student_id = None
            full_name  = "Unknown"
            color      = (0, 0, 255)  # red for unknown

            if len(distances) > 0:
                best_idx = int(np.argmin(distances))
                if matches[best_idx]:
                    identity   = id_snap[best_idx]
                    student_id = identity["student_id"]
                    full_name  = identity["name"]
                    name       = full_name
                    color      = (0, 200, 0)  # green for recognized

                    if already_marked_today(student_id):
                        if student_id not in notified_today:
                            log.info("%s already marked present today.", full_name)
                            notified_today.add(student_id)
                    elif cooldown_ok(student_id):
                        threading.Thread(
                            target=mark_attendance,
                            args=(student_id, full_name),
                            daemon=True,
                        ).start()
                else:
                    log.info("Unrecognized face detected.")

            top, right, bottom, left = location
            top    *= 4; right  *= 4
            bottom *= 4; left   *= 4

            cv2.rectangle(frame, (left, top), (right, bottom), color, 2)
            cv2.rectangle(frame, (left, bottom - 40), (right, bottom), color, cv2.FILLED)
            cv2.putText(
                frame, name,
                (left + 6, bottom - 10),
                cv2.FONT_HERSHEY_DUPLEX,
                0.65, (255, 255, 255), 1,
            )

            if student_id:
                confidence = round((1 - float(np.min(distances))) * 100, 1)
                cv2.putText(
                    frame, f"{confidence}%",
                    (left + 6, top - 8),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.5, color, 1,
                )

        cv2.putText(
            frame,
            datetime.now().strftime("%Y-%m-%d  %H:%M:%S"),
            (10, 28),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.65, (255, 255, 255), 2,
        )
        cv2.putText(
            frame,
            f"Marked today: {len(marked_today)}",
            (10, 56),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.55, (0, 255, 180), 2,
        )

        cv2.imshow("SmartAttend — Face Recognition", frame)

        if cv2.waitKey(1) & 0xFF == ord('q'):
            log.info("Quit signal received.")
            break

    except KeyboardInterrupt:
        log.info("Interrupted by user.")
        break
    except Exception as e:
        log.error("Frame processing error: %s. Continuing...", e)
        continue

if cap is not None:
    cap.release()
cv2.destroyAllWindows()
log.info("Camera closed. System stopped.")
