import cv2
import face_recognition
import logging
import os
import requests
import tempfile
import threading

from config import API_BASE_URL, API_KEY

log = logging.getLogger("smartattend")

_HEADERS = {"X-API-Key": API_KEY}


def encode_student(student: dict, dlib_lock: threading.Lock) -> list[tuple]:
    """
    Download and encode all photos for one student.

    Returns a list of (encoding, identity) tuples where identity is
    {"student_id": "STU-001", "name": "Jane Doe"}.
    Photos are written to a temp dir and deleted immediately after encoding.
    dlib_lock must be the same lock used by the recognition loop so dlib
    is never called from two threads at once.
    """
    identity = {"student_id": student["student_id"], "name": student["name"]}
    pairs: list[tuple] = []

    with tempfile.TemporaryDirectory() as tmpdir:
        for url in student.get("photo_urls", []):
            filename = url.split("/")[-1].split("?")[0]
            dest     = os.path.join(tmpdir, filename)

            try:
                resp = requests.get(url, headers=_HEADERS, timeout=15)
                resp.raise_for_status()
                with open(dest, "wb") as f:
                    f.write(resp.content)
            except Exception as e:
                log.warning("Could not download %s: %s", url, e)
                continue

            image = cv2.imread(dest)
            if image is None:
                log.warning("Could not read downloaded image: %s", filename)
                continue

            rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)

            with dlib_lock:
                boxes = face_recognition.face_locations(rgb, model="hog")
                if not boxes:
                    log.warning("No face detected in %s — skipping.", filename)
                    continue
                for enc in face_recognition.face_encodings(rgb, boxes):
                    pairs.append((enc, identity))

    return pairs


def mark_encoded(db_id: int, name: str) -> None:
    """Tell Laravel this student is now encoded."""
    url = f"{API_BASE_URL}/students/{db_id}/encoded"
    try:
        resp = requests.post(url, headers=_HEADERS, timeout=10)
        resp.raise_for_status()
        log.info("Marked %s (id=%d) as face_encoded.", name, db_id)
    except Exception as e:
        log.warning("Could not mark %s as encoded: %s", name, e)
