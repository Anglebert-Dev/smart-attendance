#!/bin/bash

# ==============================================================================
# Raspberry Pi Deployment Script for Smart Attendance (python-ai)
# Python version: 3.13.5
# ==============================================================================

set -e

echo "======================================================="
echo " Starting Smart Attendance Raspberry Pi Deployment..."
echo "======================================================="

# 1. Update system and install required system packages
echo ""
echo "[1/5] Installing system dependencies (CMake, GCC, OpenCV dependencies)..."
sudo apt-get update
sudo apt-get install -y build-essential cmake g++ \
    libgl1-mesa-glx libglib2.0-0 libsm6 libxext6 libxrender-dev

# 2. Setup Virtual Environment
echo ""
echo "[2/5] Creating Python 3.13 virtual environment..."
if ! command -v python3.13 &> /dev/null; then
    echo "ERROR: python3.13 could not be found. Please ensure Python 3.13.5 is installed on this Pi."
    exit 1
fi

python3.13 -m venv venv
source venv/bin/activate

# 3. Upgrade pip and install build tools
echo ""
echo "[3/5] Upgrading pip and installing Python build tools..."
pip install --upgrade pip
pip install setuptools wheel cmake

# 4. Install Dependencies
echo ""
echo "[4/5] Installing Python dependencies (This will take a LONG time for dlib)..."
echo "Grab a coffee, building dlib on a Raspberry Pi can take 1-2 hours!"
pip install -r requirements.txt

# 5. Create .env if it doesn't exist
echo ""
echo "[5/5] Checking configuration..."
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
    echo "Please edit the .env file with your API_KEY and API_BASE_URL."
fi

# 6. Systemd Service (Optional)
read -p "Do you want to create a systemd service to run this on boot? (y/N): " setup_service
if [[ "$setup_service" =~ ^[Yy]$ ]]; then
    SERVICE_FILE="/etc/systemd/system/smart-attendance.service"
    APP_DIR=$(pwd)
    USER_NAME=$(whoami)
    
    echo "Creating systemd service..."
    sudo bash -c "cat > $SERVICE_FILE <<EOF
[Unit]
Description=Smart Attendance Face Recognition
After=network.target

[Service]
User=$USER_NAME
WorkingDirectory=$APP_DIR
Environment=PATH=$APP_DIR/venv/bin:/usr/local/bin:/usr/bin:/bin
ExecStart=$APP_DIR/venv/bin/python recognize.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF"

    sudo systemctl daemon-reload
    sudo systemctl enable smart-attendance.service
    
    echo "Service created and enabled!"
    echo "You can start it manually with: sudo systemctl start smart-attendance.service"
    echo "You can view logs with: sudo journalctl -u smart-attendance.service -f"
fi

echo ""
echo "======================================================="
echo " Deployment script finished!"
echo " 1. Make sure to update your .env file with the correct details."
echo " 2. If running without a monitor, set HEADLESS=true in your .env."
echo " 3. Test the app by running: source venv/bin/activate && python recognize.py"
echo "======================================================="
