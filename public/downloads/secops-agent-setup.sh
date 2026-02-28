#!/bin/bash

# SecOps Agent Installer
# Usage: ./install.sh --token "TOKEN" --server "URL" --hostname "NAME"

set -e

# Defaults
INSTALL_DIR="/opt/secops_agent"
TOKEN=""
SERVER_URL=""
HOSTNAME=$(hostname)
poll_interval=5

# Parse Args
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --token) TOKEN="$2"; shift ;;
        --server) SERVER_URL="$2"; shift ;;
        --hostname) HOSTNAME="$2"; shift ;;
        *) echo "Unknown parameter passed: $1"; exit 1 ;;
    esac
    shift
done

if [ -z "$TOKEN" ] || [ -z "$SERVER_URL" ]; then
    echo "Error: --token and --server are required."
    exit 1
fi

echo ">>> SecOps Agent Installer"
echo ">>> Target: $HOSTNAME under $SERVER_URL"

# 1. Install Dependencies
echo ">>> Installing System Dependencies..."
if [ -f /etc/debian_version ]; then
    apt-get update -qq
    apt-get install -y python3 python3-venv python3-pip curl
elif [ -f /etc/redhat-release ]; then
    yum install -y python3 python3-pip
fi

# 2. Setup Directory
echo ">>> Setting up directory $INSTALL_DIR..."
mkdir -p "$INSTALL_DIR"
cd "$INSTALL_DIR"

# 3. Create Files (Agent Script) - Downloading from Manager assumes public access, but for now we write inline or curl
# In a real scenario, we would curl the python file. 
# Here we will assume the script fetches the code from the server OR we write a simple version.
# Since we can't easily sync the python file from `tools/` to `public/` without a build step,
# We will make the python script embedded here or fetch it.
# Ideally, Laravel should serve the python file too.
# Let's assume we download `secops_agent.py` and `requirements.txt` from the manager.

echo ">>> Fetching Agent Code..."
curl -s "${SERVER_URL}/downloads/secops_agent.py" -o secops_agent.py
curl -s "${SERVER_URL}/downloads/requirements.txt" -o requirements.txt

# 4. Setup Venv
echo ">>> creating venv..."
python3 -m venv venv
./venv/bin/pip install -r requirements.txt

# 5. Config
echo ">>> Configuring .env..."
cat > .env <<EOF
MANAGER_URL=${SERVER_URL}
AGENT_TOKEN=${TOKEN}
POLL_INTERVAL=${poll_interval}
AGENT_HOSTNAME=${HOSTNAME}
EOF

# 6. Systemd
echo ">>> Installing Systemd Service..."
cat > /etc/systemd/system/secops-agent.service <<EOF
[Unit]
Description=SecOps IP Block Agent
After=network.target

[Service]
User=root
Group=root
WorkingDirectory=${INSTALL_DIR}
Environment="PATH=${INSTALL_DIR}/venv/bin"
ExecStart=${INSTALL_DIR}/venv/bin/python secops_agent.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable secops-agent
systemctl restart secops-agent

echo ">>> Installation Complete! Service 'secops-agent' is running."
