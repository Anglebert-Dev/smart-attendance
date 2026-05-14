# Smart Attendance System

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://python.org)
[![OpenCV](https://img.shields.io/badge/OpenCV-5C3EE8?style=for-the-badge&logo=opencv&logoColor=white)](https://opencv.org)

A real-time face recognition attendance system that bridges a **Laravel 13** management portal with a **Python AI** recognition engine running on a local camera station.

---

## Features

### Web Portal (Laravel)

| Role | Capabilities |
|------|-------------|
| **Admin** | Manage students, teachers, classes, HODs; view all attendance; generate API keys |
| **Teacher** | View assigned classes, student lists, and attendance records |
| **HOD** | Oversight of teachers and classes within their department |

- Session-based authentication with role middleware
- API key management (SHA-256 hashed, plain key shown once)
- Student photo management — multiple photos per student
- Attendance export and filtering
- **Auto-absent scheduling**: at 17:00 every day, all students with no record are automatically marked absent

### Python AI Engine

- Downloads student photos from Laravel via API (`download_dataset.py`)
- Encodes faces into 128-d embeddings using dlib (`encode_faces.py`)
- Real-time recognition from camera feed with live bounding boxes and confidence scores (`recognize.py`)
- Anti-spam: 60-second per-student cooldown + daily reset at midnight
- **Non-blocking API calls**: attendance is posted in a background thread — camera feed never freezes
- **Camera auto-fallback**: tries indices 0 → 1 → 2 if the configured source fails
- **Auto-reconnect**: on frame failure, retries up to 5 times before exiting
- **Daily rotating logs**: written to `logs/recognize.log`, rotated at midnight, 30 days retained

---

## Architecture

```
Camera Station
     │
     ▼
Python AI (recognize.py)
  - face detection & matching
  - background thread → POST /api/v1/attendance
     │
     ▼
Laravel REST API (/api/v1/*)
  - API key validation
  - server-side timestamp (marked_at = now())
  - stores AttendanceRecord
     │
     ▼
MySQL Database
     │
     ▼
Web Portal (Admin / Teacher / HOD dashboards)
```

---

## Project Structure

```
smart-attendance/
├── app/
│   ├── Console/Commands/
│   │   └── MarkAbsentStudents.php   # artisan attendance:mark-absent
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Teacher/
│   │   │   ├── Hod/
│   │   │   └── Api/AttendanceApiController.php
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php
│   │       ├── TeacherMiddleware.php
│   │       ├── HodMiddleware.php
│   │       └── ApiKeyMiddleware.php
│   └── Models/
│       ├── Student.php
│       ├── SchoolClass.php
│       ├── AttendanceRecord.php
│       ├── StudentPhoto.php
│       └── ApiKey.php
├── config/
│   └── cors.php                     # API CORS — localhost only
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php                  # scheduler: mark-absent at 17:00
└── python-ai/
    ├── config.py
    ├── download_dataset.py          # fetch photos from Laravel
    ├── encode_faces.py              # generate face embeddings
    ├── recognize.py                 # live recognition loop
    ├── dataset/                     # raw student images (gitignored)
    ├── encodings/                   # encodings.pkl (gitignored)
    ├── logs/                        # daily rotating logs (gitignored)
    └── requirements.txt
```

---

## Getting Started

### Requirements

- PHP 8.3+ and Composer
- MySQL
- Node.js and npm
- Python 3.10+ with a working `dlib` build
- A webcam or USB camera

### 1. Backend Setup

```bash
composer install
cp .env.example .env
# Fill in DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env

php artisan key:generate
php artisan migrate --seed
php artisan storage:link

npm install && npm run build

php artisan serve
```

### 2. Scheduler Setup (auto-absent at 17:00)

Add this single line to your crontab (`crontab -e`):

```
* * * * * cd /path/to/smart-attendance && php artisan schedule:run >> /dev/null 2>&1
```

To trigger it manually for any date:

```bash
php artisan attendance:mark-absent
php artisan attendance:mark-absent --date=2026-05-06
```

### 3. Generate an API Key

1. Log in as Admin → **API Keys** → create a new key
2. Copy the plain key shown — it is only displayed once

### 4. Python AI Engine Setup

```bash
cd python-ai
python3 -m venv .venv
source .venv/bin/activate        # Windows: .venv\Scripts\activate
pip install -r requirements.txt

cp .env.example .env
# Set API_BASE_URL and API_KEY in python-ai/.env
```

### 5. Prepare the Dataset

```bash
# Download student photos from Laravel (requires server to be running)
python3 download_dataset.py

# Encode faces (generates encodings/encodings.pkl)
python3 encode_faces.py
```

### 6. Run the Recognition Engine

```bash
python3 recognize.py
```

Press `Q` in the camera window to stop. Logs are written to `logs/recognize.log` and rotated daily.

---

## API Endpoints

All routes require `X-API-Key` header and are throttled at 60 req/min.

| Method | Path | Description |
|--------|------|-------------|
| `POST` | `/api/v1/attendance` | Mark a student present (called by Python engine) |
| `GET` | `/api/v1/students/for-encoding` | List students with photo URLs for encoding |
| `POST` | `/api/v1/students/{id}/encoded` | Flag a student as face-encoded |

---

## Environment Variables

### Laravel (`.env`)

| Key | Description |
|-----|-------------|
| `DB_DATABASE` | MySQL database name |
| `DB_USERNAME` | MySQL username |
| `DB_PASSWORD` | MySQL password |
| `APP_URL` | Base URL (e.g. `http://localhost:8000`) |

### Python AI (`python-ai/.env`)

| Key | Default | Description |
|-----|---------|-------------|
| `API_BASE_URL` | `http://localhost:8000/api/v1` | Laravel API base URL |
| `API_KEY` | — | Key from Admin → API Keys |
| `CAMERA_SOURCE` | `0` | Camera index or `/dev/videoX` path |
| `TOLERANCE` | `0.5` | Face match tolerance (lower = stricter) |
| `PROCESS_EVERY_N_FRAMES` | `3` | Process 1 in every N frames (CPU saving) |
| `MARK_COOLDOWN_SECONDS` | `60` | Seconds between re-attempts per student |

---

## Security

- API keys stored as SHA-256 hashes; plain key wiped after first retrieval
- `marked_at` timestamp set server-side — client cannot spoof the time
- CORS restricted to `localhost` / `127.0.0.1` for API routes
- Login endpoint throttled to 5 attempts per minute per IP
- Photo uploads validated: `jpeg`, `jpg`, `png`, `webp` only, max 5 MB, min 100×100 px

---

## License

Distributed under the MIT License.
