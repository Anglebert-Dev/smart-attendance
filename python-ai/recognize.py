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
)

os.makedirs("logs", exist_ok=True)

log = logging.getLogger("smartattend")
log.setLevel(logging.DEBUG)

_fmt = logging.Formatter(
    "%(asctime)s  %(levelname)-8s  %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)

_fh = TimedRotatingFileHandler(
    "logs/recognize.log",
    when="midnight",
    backupCount=30,
    encoding="utf-8",
)
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
    known_identities = data["identities"]  # [{"student_id": "STU001", "name": "John Doe"}, ...]
    log.info("Loaded %d known face(s)", len(known_identities))
except FileNotFoundError:
    log.error("encodings/encodings.pkl not found. Run encode_faces.py first!")
    exit(1)

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

        locations  = face_recognition.face_locations(rgb)
        encodings  = face_recognition.face_encodings(rgb, locations)

        for encoding, location in zip(encodings, locations):
            distances = face_recognition.face_distance(known_encodings, encoding)
            matches   = face_recognition.compare_faces(known_encodings, encoding, tolerance=TOLERANCE)

            name       = "Unknown"
            student_id = None
            full_name  = "Unknown"
            color      = (0, 0, 255)  # red for unknown

            if len(distances) > 0:
                best_idx = int(np.argmin(distances))
                if matches[best_idx]:
                    identity   = known_identities[best_idx]
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
