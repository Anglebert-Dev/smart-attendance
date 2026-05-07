import os
from dotenv import load_dotenv

load_dotenv()

os.environ.setdefault("QT_QPA_PLATFORM", os.getenv("QT_QPA_PLATFORM", "xcb"))

import cv2
import face_recognition
import pickle
import numpy as np
import requests
import time
from datetime import datetime

from config import (
    API_BASE_URL,
    API_KEY,
    CAMERA_SOURCE,
    TOLERANCE,
    PROCESS_EVERY_N_FRAMES,
    MARK_COOLDOWN_SECONDS,
)

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

print("[INFO] Loading face encodings...")
try:
    with open("encodings/encodings.pkl", "rb") as f:
        data = pickle.load(f)
    known_encodings  = data["encodings"]
    known_identities = data["identities"]  # [{"student_id": "STU001", "name": "John Doe"}, ...]
    print(f"[INFO] Loaded {len(known_identities)} known face(s)\n")
except FileNotFoundError:
    print("encodings/encodings.pkl not found. Run encode_faces.py first!")
    exit(1)

# ── API Call ──────────────────────────────────────────────────────────
def mark_attendance(student_id: str, full_name: str):
    """Send attendance record to Laravel API."""
    try:
        response = requests.post(
            f"{API_BASE_URL}/attendance",
            json={
                "student_id": student_id,
            },
            headers={"X-API-Key": API_KEY},
            timeout=5
        )

        if response.status_code == 200:
            data = response.json()

            if data.get("already_marked"):
                record_marked(student_id)
                if student_id not in notified_today:
                    print(f"[INFO] {full_name} ({student_id}) already marked earlier today.")
                    notified_today.add(student_id)
                return

            class_name = data.get("class", "N/A")
            time_str   = datetime.now().strftime("%H:%M:%S")
            print(f"[✓] Marked: {full_name} ({student_id}) | Class: {class_name} | {time_str}")
            record_marked(student_id)

        elif response.status_code == 404:
            print(f"[WARN] Student {student_id} not found in Laravel database.")

        else:
            print(f"[ERROR] API returned {response.status_code} for {student_id}")

    except requests.exceptions.ConnectionError:
        print(f"[WARN] Cannot reach API — is Laravel running? Skipping {student_id}")
    except requests.exceptions.Timeout:
        print(f"[WARN] API timeout for {student_id}")

def open_camera(primary_source):
    sources = (
        [primary_source] if not isinstance(primary_source, int)
        else [primary_source] + [i for i in range(3) if i != primary_source]
    )
    for src in sources:
        print(f"[INFO] Trying camera: {src}")
        cam = cv2.VideoCapture(src)
        if cam.isOpened():
            print(f"[INFO] Opened camera: {src}")
            return cam
        cam.release()
    return None

cap = open_camera(CAMERA_SOURCE)

if cap is None:
    print(f" Could not open any camera (tried {CAMERA_SOURCE} and fallbacks).")
    print("   Check: ls /dev/video*")
    exit(1)

cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)

print("[INFO] Recognition running. Press Q to quit.\n")

frame_count = 0
today_str   = datetime.now().strftime("%Y-%m-%d")

while True:
    try:
        ret, frame = cap.read()
        if not ret:
            print("[ERROR] Failed to grab frame from camera.")
            break

        current_day = datetime.now().strftime("%Y-%m-%d")
        if current_day != today_str:
            print(f"\n[INFO] New day detected ({current_day}). Resetting attendance tracking.\n")
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
            distances   = face_recognition.face_distance(known_encodings, encoding)
            matches     = face_recognition.compare_faces(known_encodings, encoding, tolerance=TOLERANCE)

            name        = "Unknown"
            student_id  = None
            full_name   = "Unknown"
            color       = (0, 0, 255)  # red for unknown

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
                            print(f"[INFO] {full_name} already marked present today.")
                            notified_today.add(student_id)
                    elif cooldown_ok(student_id):
                        mark_attendance(student_id, full_name)
                else:
                    print(f"[UNKNOWN] Unrecognized face detected at {datetime.now().strftime('%H:%M:%S')}")

            top, right, bottom, left = location
            top    *= 4; right  *= 4
            bottom *= 4; left   *= 4

            cv2.rectangle(frame, (left, top), (right, bottom), color, 2)

            cv2.rectangle(frame, (left, bottom - 40), (right, bottom), color, cv2.FILLED)

            cv2.putText(
                frame, name,
                (left + 6, bottom - 10),
                cv2.FONT_HERSHEY_DUPLEX,
                0.65, (255, 255, 255), 1
            )

            if student_id:
                confidence = round((1 - float(np.min(distances))) * 100, 1)
                cv2.putText(
                    frame, f"{confidence}%",
                    (left + 6, top - 8),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.5, color, 1
                )

        cv2.putText(
            frame,
            datetime.now().strftime("%Y-%m-%d  %H:%M:%S"),
            (10, 28),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.65, (255, 255, 255), 2
        )
        cv2.putText(
            frame,
            f"Marked today: {len(marked_today)}",
            (10, 56),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.55, (0, 255, 180), 2
        )

        cv2.imshow("SmartAttend — Face Recognition", frame)

        if cv2.waitKey(1) & 0xFF == ord('q'):
            print("\n[INFO] Quit signal received.")
            break

    except KeyboardInterrupt:
        print("\n[INFO] Interrupted by user.")
        break
    except Exception as e:
        print(f"[ERROR] Frame processing error: {e}. Continuing...")
        continue

cap.release()
cv2.destroyAllWindows()
print("[INFO] Camera closed. System stopped.")