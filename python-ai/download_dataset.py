import os
import requests
from pathlib import Path
from config import API_STUDENTS_URL, API_KEY

DATASET_DIR = Path("dataset")


def sanitize(name: str) -> str:
    """Turn 'John Doe' into 'John_Doe' for safe folder names."""
    return name.strip().replace(" ", "_")


def folder_name(student_id: str, name: str) -> str:
    return f"{student_id}__{sanitize(name)}"


print("[INFO] Fetching student list from Laravel...\n")

try:
    resp = requests.get(API_STUDENTS_URL, headers={"X-API-Key": API_KEY}, timeout=10)
    resp.raise_for_status()
except requests.exceptions.ConnectionError:
    print("[ERROR] Could not reach the Laravel API.")
    print(f"        Is the server running at {API_STUDENTS_URL}?")
    exit(1)
except requests.exceptions.HTTPError as e:
    print(f"[ERROR] API returned {resp.status_code}: {resp.text}")
    exit(1)

students = resp.json().get("students", [])

if not students:
    print("[WARNING] No students with photos found.")
    print("          Add students with photos in Admin → Students first.")
    exit(0)

print(f"[INFO] Found {len(students)} student(s) with photos.\n")

downloaded = 0
skipped    = 0
errors     = 0

for s in students:
    sid   = s["student_id"]
    name  = s["name"]
    urls  = s.get("photo_urls", [])
    label = f"{name} ({sid})"

    if not urls:
        print(f"  [SKIP] {label} — no photos")
        skipped += 1
        continue

    # Create dataset/STU001__John_Doe/
    folder = DATASET_DIR / folder_name(sid, name)
    folder.mkdir(parents=True, exist_ok=True)

    print(f"  {label} — {len(urls)} photo(s)")

    for url in urls:
        filename = url.split("/")[-1]
        dest     = folder / filename

        if dest.exists():
            print(f"    [SKIP] {filename} — already exists")
            skipped += 1
            continue

        try:
            img_resp = requests.get(url, timeout=15)
            img_resp.raise_for_status()
            dest.write_bytes(img_resp.content)
            print(f"    [OK]   {filename}")
            downloaded += 1
        except Exception as e:
            print(f"    [ERROR] {filename} — {e}")
            errors += 1

print(f"\n[DONE] {downloaded} downloaded, {skipped} skipped, {errors} errors.")

if downloaded > 0:
    print("\nNext step → run:  python encode_faces.py")
