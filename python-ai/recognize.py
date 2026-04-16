import face_recognition
import cv2
import pickle
import numpy as np
import requests
import time
from datetime import datetime

from config import (
    API_ATTENDANCE_URL,
    API_KEY,
    CAMERA_SOURCE,
    TOLERANCE,
    PROCESS_EVERY_N_FRAMES,
    MARK_COOLDOWN_SECONDS,
)

TEST_MODE = False

print("[INFO] Loading face encodings...")

try:
    with open("encodings/encodings.pkl", "rb") as f:
        data = pickle.load(f)
except FileNotFoundError:
    print("[ERROR] encodings/encodings.pkl not found.")
    print("        Run encode_faces.py first.")
    exit(1)

known_encodings  = data["encodings"]
known_identities = data.get("identities")

if known_identities is None:
    print("[WARNING] Old encoding format detected — no student_id available.")
    print("          Re-run encode_faces.py with the new STU001__Name folder format.")
    known_identities = [{"student_id": n, "name": n} for n in data.get("names", [])]

print(f"[INFO] Loaded {len(known_identities)} known face(s)\n")

last_marked: dict[str, float] = {}


def cooldown_ok(student_id: str) -> bool:
    """Returns True if the student can be marked again (cooldown expired)."""
    now  = time.time()
    last = last_marked.get(student_id, 0)
    if now - last >= MARK_COOLDOWN_SECONDS:
        last_marked[student_id] = now
        return True
    remaining = int(MARK_COOLDOWN_SECONDS - (now - last))
    print(f"[SKIP] {student_id} — cooldown active ({remaining}s remaining)")
    return False


def mark_attendance(identity: dict) -> None:
    """POST attendance to the Laravel API using student_id."""
    student_id   = identity["student_id"]
    display_name = identity["name"]

    if not cooldown_ok(student_id):
        return

    if TEST_MODE:
        print(f"[TEST] Detected: {display_name} ({student_id}) at {datetime.now().strftime('%H:%M:%S')}")
        return

    payload = {
        "student_id": student_id,
        "timestamp":  datetime.now().isoformat(),
    }
    headers = {"X-API-Key": API_KEY}

    try:
        response = requests.post(
            API_ATTENDANCE_URL,
            json=payload,
            headers=headers,
            timeout=5,
        )

        if response.status_code == 200:
            data = response.json()
            print(
                f"[✓] Marked: {display_name} ({student_id}) "
                f"| Class: {data.get('class', 'N/A')} "
                f"| {datetime.now().strftime('%H:%M:%S')}"
            )
        elif response.status_code == 409:
            print(f"[=] Already marked today: {display_name}")
        else:
            print(f"[ERROR] API returned {response.status_code} for {student_id}: {response.text}")
            last_marked.pop(student_id, None)

    except requests.exceptions.ConnectionError:
        print(f"[OFFLINE] Could not reach API — {display_name} queued locally")


print(f"[INFO] Opening camera: {CAMERA_SOURCE}")
cap = cv2.VideoCapture(CAMERA_SOURCE)

if not cap.isOpened():
    print("[ERROR] Could not open camera.")
    exit(1)

frame_count = 0
print("[INFO] Recognition running. Press Q to quit.\n")

while True:
    ret, frame = cap.read()
    if not ret:
        print("[ERROR] Failed to read frame from camera.")
        break

    frame_count += 1

    if frame_count % PROCESS_EVERY_N_FRAMES != 0:
        cv2.imshow("SmartAttend — Face Recognition", frame)
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break
        continue

    small = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb   = cv2.cvtColor(small, cv2.COLOR_BGR2RGB)

    face_locations = face_recognition.face_locations(rgb)
    face_encodings = face_recognition.face_encodings(rgb, face_locations)

    for face_enc, face_loc in zip(face_encodings, face_locations):
        matches        = face_recognition.compare_faces(known_encodings, face_enc, tolerance=TOLERANCE)
        face_distances = face_recognition.face_distance(known_encodings, face_enc)

        label = "Unknown"
        color = (0, 0, 255)  # red

        if len(face_distances) > 0:
            best = int(np.argmin(face_distances))
            if matches[best]:
                identity = known_identities[best]
                label    = f"{identity['name']} ({identity['student_id']})"
                color    = (0, 200, 80)  # green
                mark_attendance(identity)

        top, right, bottom, left = [v * 4 for v in face_loc]

        cv2.rectangle(frame, (left, top), (right, bottom), color, 2)
        cv2.rectangle(frame, (left, bottom - 35), (right, bottom), color, cv2.FILLED)
        cv2.putText(frame, label, (left + 6, bottom - 8),
                    cv2.FONT_HERSHEY_DUPLEX, 0.65, (255, 255, 255), 1)

    ts = datetime.now().strftime("%Y-%m-%d  %H:%M:%S")
    cv2.putText(frame, ts, (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (220, 220, 220), 2)

    cv2.imshow("SmartAttend — Face Recognition", frame)

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()
print("\n[INFO] System stopped.")
