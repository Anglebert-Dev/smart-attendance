

from dotenv import load_dotenv
import os

load_dotenv()

API_BASE_URL = os.getenv("API_BASE_URL", "http://localhost:8000/api/v1")
API_KEY      = os.getenv("API_KEY")

API_ATTENDANCE_URL   = f"{API_BASE_URL}/attendance"
API_STUDENTS_URL     = f"{API_BASE_URL}/students/for-encoding"
API_MARK_ENCODED_URL = f"{API_BASE_URL}/students/{{id}}/encoded"

_camera_raw = os.getenv("CAMERA_SOURCE", "0")
try:
    CAMERA_SOURCE = int(_camera_raw)
except ValueError:
    CAMERA_SOURCE = _camera_raw            

TOLERANCE              = float(os.getenv("TOLERANCE", "0.5"))
PROCESS_EVERY_N_FRAMES = int(os.getenv("PROCESS_EVERY_N_FRAMES", "3"))
MARK_COOLDOWN_SECONDS  = int(os.getenv("MARK_COOLDOWN_SECONDS", "60"))
QT_QPA_PLATFORM        = os.getenv("QT_QPA_PLATFORM", "xcb")

if not API_KEY:
    raise RuntimeError(
        "\n\n[ERROR] API_KEY is not set!\n"
        "  1. Copy .env.example → .env\n"
        "  2. Generate a key at Admin → API Keys\n"
        "  3. Paste it as API_KEY=... in python-ai/.env\n"
    )
