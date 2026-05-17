#!/bin/bash

# ==============================================================================
# Script to create and start the Smart Attendance systemd service
# Run this inside the python-ai folder
# ==============================================================================

SERVICE_FILE="/etc/systemd/system/smart-attendance.service"
APP_DIR=$(pwd)
USER_NAME=$(whoami)

echo "Creating systemd service file at $SERVICE_FILE..."

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

echo "Reloading systemd daemon..."
sudo systemctl daemon-reload

echo "Enabling service to start on boot..."
sudo systemctl enable smart-attendance.service

echo "Starting the service now..."
sudo systemctl start smart-attendance.service

echo "======================================================="
echo "Done! The service is now running in the background."
echo "To view live logs, run:"
echo "  sudo journalctl -u smart-attendance.service -f"
echo "======================================================="
