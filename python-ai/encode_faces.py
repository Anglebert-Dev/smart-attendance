
import face_recognition
import os
import pickle
import cv2
from pathlib import Path

DATASET_PATH   = "dataset"
ENCODINGS_PATH = "encodings/encodings.pkl"


def parse_folder_name(folder_name: str) -> dict:
    """
    Parse 'STU001__John_Doe' → {'student_id': 'STU001', 'name': 'John Doe'}
    Falls back gracefully if the __ separator is missing (legacy support).
    """
    if "__" in folder_name:
        student_id, raw_name = folder_name.split("__", 1)
        display_name = raw_name.replace("_", " ")
    else:
        # Legacy: treat entire folder name as both id and display name
        student_id   = folder_name
        display_name = folder_name.replace("_", " ")
    return {"student_id": student_id.strip(), "name": display_name.strip()}


print("[INFO] Starting face encoding...")
print(f"[INFO] Dataset path: {os.path.abspath(DATASET_PATH)}\n")

known_encodings = []
known_identities = []   # [{"student_id": "STU001", "name": "John Doe"}, ...]

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

        boxes = face_recognition.face_locations(rgb_image, model="hog")

        if not boxes:
            print(f"  [WARNING] No face detected in {image_file}, skipping...")
            continue

        encodings = face_recognition.face_encodings(rgb_image, boxes)

        for encoding in encodings:
            known_encodings.append(encoding)
            known_identities.append(identity)
            encoded_count += 1

        print(f"  [OK] {image_file}")

    print(f"  → {encoded_count} encoding(s) added for {identity['name']}\n")

if not known_encodings:
    print("[WARNING] No encodings were generated. Check your dataset folder.")
else:
    os.makedirs("encodings", exist_ok=True)
    data = {
        "encodings":  known_encodings,
        "identities": known_identities,  # structured: student_id + name
    }
    with open(ENCODINGS_PATH, "wb") as f:
        pickle.dump(data, f)

    unique_students = len({i["student_id"] for i in known_identities})
    print(f"[DONE] {len(known_encodings)} encoding(s) for {unique_students} student(s)")
    print(f"[DONE] Saved to {ENCODINGS_PATH}")
