
import face_recognition
import os
import pickle
import cv2
import requests
from pathlib import Path
from config import API_STUDENTS_URL, API_KEY

DATASET_PATH   = "dataset"
ENCODINGS_PATH = "encodings/encodings.pkl"

# Base URL for markEncoded: POST /api/v1/students/{id}/encoded
_API_BASE = API_STUDENTS_URL.replace("/students/for-encoding", "")
MARK_ENCODED_URL = _API_BASE + "/students/{db_id}/encoded"
HEADERS = {"X-API-Key": API_KEY}


def parse_folder_name(folder_name: str) -> dict:
    """
    Parse 'STU001__John_Doe' → {'student_id': 'STU001', 'name': 'John Doe'}
    Falls back gracefully if the __ separator is missing (legacy support).
    """
    if "__" in folder_name:
        student_id, raw_name = folder_name.split("__", 1)
        display_name = raw_name.replace("_", " ")
    else:
        student_id   = folder_name
        display_name = folder_name.replace("_", " ")
    return {"student_id": student_id.strip(), "name": display_name.strip()}


def fetch_student_id_map() -> dict:
    """
    Returns a dict mapping student_id string → database int id.
    Example: {'STU-001': 1, 'STU-002': 2}
    """
    try:
        resp = requests.get(API_STUDENTS_URL, headers=HEADERS, timeout=10)
        resp.raise_for_status()
        students = resp.json().get("students", [])
        return {s["student_id"]: s["id"] for s in students}
    except Exception as e:
        print(f"[WARNING] Could not fetch student list from API: {e}")
        print("          markEncoded calls will be skipped.\n")
        return {}


def mark_encoded(db_id: int, name: str) -> None:
    """Call POST /api/v1/students/{db_id}/encoded to flag the student as encoded."""
    try:
        url  = MARK_ENCODED_URL.format(db_id=db_id)
        resp = requests.post(url, headers=HEADERS, timeout=10)
        resp.raise_for_status()
        print(f"  [API] Marked {name} (db_id={db_id}) as face_encoded ✓")
    except Exception as e:
        print(f"  [API WARNING] Could not mark {name} as encoded: {e}")


# ── Fetch student map before encoding ────────────────────────────────────────
print("[INFO] Starting face encoding...")
print(f"[INFO] Dataset path: {os.path.abspath(DATASET_PATH)}\n")
print("[INFO] Fetching student ID map from Laravel API...")
student_id_map = fetch_student_id_map()
if student_id_map:
    print(f"[INFO] Loaded {len(student_id_map)} student(s) from API.\n")

known_encodings  = []
known_identities = []   # [{"student_id": "STU001", "name": "John Doe"}, ...]
encoded_students = set()  # track which students were successfully encoded

for folder_name in sorted(os.listdir(DATASET_PATH)):
    student_folder = os.path.join(DATASET_PATH, folder_name)

    if not os.path.isdir(student_folder):
        continue

    identity = parse_folder_name(folder_name)
    print(f"[INFO] Processing: {identity['name']} (ID: {identity['student_id']})")

    image_files = [
        f for f in os.listdir(student_folder)
        if f.lower().endswith((".jpg", ".jpeg", ".png", ".webp"))
    ]

    if not image_files:
        print(f"  [WARNING] No images found in {folder_name}, skipping...")
        continue

    encoded_count = 0

    for image_file in image_files:
        image_path = os.path.join(student_folder, image_file)

        image = cv2.imread(image_path)
        if image is None:
            print(f"  [WARNING] Could not read {image_file}, skipping...")
            continue

        rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        boxes     = face_recognition.face_locations(rgb_image, model="hog")

        if not boxes:
            print(f"  [WARNING] No face detected in {image_file}, skipping...")
            continue

        encodings = face_recognition.face_encodings(rgb_image, boxes)

        for encoding in encodings:
            known_encodings.append(encoding)
            known_identities.append(identity)
            encoded_count += 1

        print(f"  [OK] {image_file}")

    if encoded_count > 0:
        encoded_students.add(identity["student_id"])

    print(f"  → {encoded_count} encoding(s) added for {identity['name']}\n")

if not known_encodings:
    print("[WARNING] No encodings were generated. Check your dataset folder.")
else:
    # ── Save encodings file ───────────────────────────────────────────────────
    os.makedirs("encodings", exist_ok=True)
    data = {
        "encodings":  known_encodings,
        "identities": known_identities,
    }
    with open(ENCODINGS_PATH, "wb") as f:
        pickle.dump(data, f)

    unique_students = len({i["student_id"] for i in known_identities})
    print(f"[DONE] {len(known_encodings)} encoding(s) for {unique_students} student(s)")
    print(f"[DONE] Saved to {ENCODINGS_PATH}\n")

    # ── Mark each successfully encoded student via API ────────────────────────
    if student_id_map:
        print("[INFO] Marking encoded students in Laravel...")
        for sid in encoded_students:
            db_id = student_id_map.get(sid)
            if db_id:
                mark_encoded(db_id, sid)
            else:
                print(f"  [WARNING] No DB id found for student_id={sid}, skipping markEncoded.")
        print()
