import os
from dotenv import load_dotenv

load_dotenv()

# Must be set before importing cv2
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

# ── State ─────────────────────────────────────────────────────────────
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

# ── Load Encodings ────────────────────────────────────────────────────
print("[INFO] Loading face encodings...")
try:
    with open("encodings/encodings.pkl", "rb") as f:
        data = pickle.load(f)
    known_encodings  = data["encodings"]
    known_identities = data["identities"]  # [{"student_id": "STU001", "name": "John Doe"}, ...]
    print(f"[INFO] Loaded {len(known_identities)} known face(s)\n")
except FileNotFoundError:
    print("❌ encodings/encodings.pkl not found. Run encode_faces.py first!")
    exit(1)

# ── API Call ──────────────────────────────────────────────────────────
def mark_attendance(student_id: str, full_name: str):
    """Send attendance record to Laravel API."""
    try:
        response = requests.post(
            f"{API_BASE_URL}/attendance",
            json={
                "student_id": student_id,
                "timestamp":  datetime.now().isoformat(),
            },
            headers={"X-API-Key": API_KEY},
            timeout=5
        )

        if response.status_code == 200:
            data = response.json()

            if data.get("already_marked"):
                # Laravel says already marked (e.g. script was restarted)
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

# ── Camera ────────────────────────────────────────────────────────────
print(f"[INFO] Opening camera: {CAMERA_SOURCE}")
cap = cv2.VideoCapture(CAMERA_SOURCE)

if not cap.isOpened():
    print(f"❌ Could not open camera {CAMERA_SOURCE}.")
    print("   Try changing CAMERA_SOURCE in .env to 1 or 2")
    print("   Or check: ls /dev/video*")
    exit(1)

# Improve camera resolution
cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)

print("[INFO] Recognition running. Press Q to quit.\n")

frame_count = 0
today_str   = datetime.now().strftime("%Y-%m-%d")

while True:
    ret, frame = cap.read()
    if not ret:
        print("[ERROR] Failed to grab frame from camera.")
        break

    # Reset daily tracking at midnight
    current_day = datetime.now().strftime("%Y-%m-%d")
    if current_day != today_str:
        print(f"\n[INFO] New day detected ({current_day}). Resetting attendance tracking.\n")
        marked_today.clear()
        notified_today.clear()
        today_str = current_day

    frame_count += 1

    # ── Process every Nth frame to save CPU ──
    if frame_count % PROCESS_EVERY_N_FRAMES != 0:
        cv2.imshow("SmartAttend — Face Recognition", frame)
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break
        continue

    # ── Shrink frame for faster detection ──
    small = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb   = cv2.cvtColor(small, cv2.COLOR_BGR2RGB)

    # ── Detect faces ──
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

                # ── Mark attendance logic ──
                if already_marked_today(student_id):
                    if student_id not in notified_today:
                        print(f"[INFO] {full_name} already marked present today.")
                        notified_today.add(student_id)
                elif cooldown_ok(student_id):
                    mark_attendance(student_id, full_name)
            else:
                print(f"[UNKNOWN] Unrecognized face detected at {datetime.now().strftime('%H:%M:%S')}")

        # ── Draw bounding box and name on frame ──
        top, right, bottom, left = location
        top    *= 4; right  *= 4
        bottom *= 4; left   *= 4

        # Box
        cv2.rectangle(frame, (left, top), (right, bottom), color, 2)

        # Name label background
        cv2.rectangle(frame, (left, bottom - 40), (right, bottom), color, cv2.FILLED)

        # Name text
        cv2.putText(
            frame, name,
            (left + 6, bottom - 10),
            cv2.FONT_HERSHEY_DUPLEX,
            0.65, (255, 255, 255), 1
        )

        # Show confidence %
        if student_id:
            confidence = round((1 - float(np.min(distances))) * 100, 1)
            cv2.putText(
                frame, f"{confidence}%",
                (left + 6, top - 8),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.5, color, 1
            )

    # ── Overlay: date/time and student count ──
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

cap.release()
cv2.destroyAllWindows()
print("[INFO] Camera closed. System stopped.")