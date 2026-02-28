import time
import requests
import subprocess
import os
import sys
import logging
import argparse
import platform

# Constants
SERVICE_NAME = "secops-agent"
INSTALL_DIR = "/opt/secops_agent"

# Logging Setup (Will be re-configured after install if needed)
logging.basicConfig(
    format='%(asctime)s [%(levelname)s] %(message)s',
    level=logging.INFO,
    handlers=[logging.StreamHandler(sys.stdout)]
)

def install_agent(server_url, token, name):
    """Performs the installation and setup of the agent."""
    print(f"🚀 Starting SecOps Agent Installation for '{name}'...")
    print(f"📡 Manager: {server_url}")

    # 1. Check Root
    if os.geteuid() != 0:
        print("❌ Error: Installation requires root privileges. Please run with sudo.")
        sys.exit(1)

    # 2. Prepare Directory
    if not os.path.exists(INSTALL_DIR):
        print(f"📂 Creating directory {INSTALL_DIR}...")
        os.makedirs(INSTALL_DIR)
    
    # 3. Copy Script to Install Dir
    current_script = os.path.abspath(__file__)
    target_script = os.path.join(INSTALL_DIR, "secops_agent.py")
    
    if current_script != target_script:
        print(f"Mw Copying agent script to {target_script}...")
        subprocess.run(["cp", current_script, target_script], check=True)

    # 4. Create .env
    env_path = os.path.join(INSTALL_DIR, ".env")
    print(f"📝 Configuring {env_path}...")
    with open(env_path, "w") as f:
        f.write(f"MANAGER_URL={server_url}\n")
        f.write(f"AGENT_TOKEN={token}\n")
        f.write(f"AGENT_HOSTNAME={name}\n")
        f.write(f"POLL_INTERVAL=5\n")

    # 5. Install Dependencies (Minimal)
    # We assume python3-requests is available or we try to install it
    print("📦 Installing dependencies...")
    try:
        subprocess.run([sys.executable, "-m", "pip", "install", "requests", "python-dotenv"], check=True)
    except subprocess.CalledProcessError:
        print("⚠️ Pip install failed. Attempting apt/yum...")
        if os.path.exists("/usr/bin/apt-get"):
            subprocess.run(["apt-get", "update", "-qq"], check=False)
            subprocess.run(["apt-get", "install", "-y", "python3-requests", "python3-dotenv"], check=False)

    # 6. Create Service
    service_content = f"""[Unit]
Description=SecOps IP Block Agent
After=network.target

[Service]
User=root
Group=root
WorkingDirectory={INSTALL_DIR}
ExecStart={sys.executable} {target_script}
Restart=always
RestartSec=10
EnvironmentFile={env_path}

[Install]
WantedBy=multi-user.target
"""
    service_path = "/etc/systemd/system/secops-agent.service"
    print(f"⚙️  Creating systemd service at {service_path}...")
    with open(service_path, "w") as f:
        f.write(service_content)

    # 7. Enable and Start
    print("▶️  Starting service...")
    subprocess.run(["systemctl", "daemon-reload"], check=True)
    subprocess.run(["systemctl", "enable", SERVICE_NAME], check=True)
    subprocess.run(["systemctl", "restart", SERVICE_NAME], check=True)

    print("✅ Installation Complete! Agent should be running.")
    print(f"Logs: journalctl -u {SERVICE_NAME} -f")


def load_config():
    """Loads configuration from .env or environment variables."""
    # Manual simple parsing to avoid python-dotenv dependency if not strictly needed for loading
    config = {}
    env_path = os.path.join(INSTALL_DIR, ".env")
    
    # Defaults
    config['MANAGER_URL'] = os.getenv('MANAGER_URL')
    config['AGENT_TOKEN'] = os.getenv('AGENT_TOKEN')
    config['AGENT_HOSTNAME'] = os.getenv('AGENT_HOSTNAME', platform.node())
    config['POLL_INTERVAL'] = int(os.getenv('POLL_INTERVAL', 5))

    if os.path.exists(env_path):
        with open(env_path, 'r') as f:
            for line in f:
                if '=' in line and not line.startswith('#'):
                    key, val = line.strip().split('=', 1)
                    config[key] = val
    
    return config

def run_ip_command(cmd_type, ip, port=None, protocol='tcp'):
    """Executes iptables command."""
    ipt_cmd = ['iptables', '-D' if cmd_type == 'unblock' else '-I', 'INPUT', '-s', ip, '-j', 'DROP']
    
    if port:
        ipt_cmd.extend(['-p', protocol, '--dport', str(port)])
    elif protocol != 'tcp': # UDP/ICMP without specific port
        ipt_cmd.extend(['-p', protocol])
        
    logging.info(f"Exec: {' '.join(ipt_cmd)}")
    try:
        result = subprocess.run(ipt_cmd, capture_output=True, text=True)
        if result.returncode == 0:
            return True, "Success"
        else:
            return False, result.stderr.strip()
    except Exception as e:
        return False, str(e)

def heartbeat(config):
    """Main polling loop."""
    url = f"{config['MANAGER_URL']}/api/agent/heartbeat"
    token = config['AGENT_TOKEN']
    hostname = config['AGENT_HOSTNAME']
    
    headers = {
        'Authorization': f"Bearer {token}",
        'Accept': 'application/json',
        'User-Agent': 'SecOps-Agent/1.0'
    }
    
    payload = {
        'metadata': {
            'hostname': hostname,
            'os': platform.system(),
            'platform': platform.platform()
        }
    }

    try:
        resp = requests.post(url, json=payload, headers=headers, timeout=10)
        if resp.status_code == 200:
            data = resp.json()
            for cmd in data.get('commands', []):
                process_command(cmd, config)
        elif resp.status_code == 401:
            logging.error("401 Unauthorized - Check Token")
    except Exception as e:
        logging.error(f"Heartbeat Error: {e}")

def process_command(cmd, config):
    cid = cmd['id']
    logging.info(f"Processing Command {cid}: {cmd['type']} {cmd['ip_address']}")
    
    success, msg = run_ip_command(
        cmd['type'], 
        cmd['ip_address'], 
        cmd.get('port'), 
        cmd.get('protocol', 'tcp')
    )
    
    # ACK
    try:
        requests.post(
            f"{config['MANAGER_URL']}/api/agent/ack",
            json={'command_id': cid, 'status': 'success' if success else 'error', 'message': msg},
            headers={'Authorization': f"Bearer {config['AGENT_TOKEN']}"},
            timeout=5
        )
    except Exception as e:
        logging.error(f"Ack failed: {e}")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='SecOps Agent')
    parser.add_argument('--install', action='store_true', help='Install and setup the agent')
    parser.add_argument('--token', help='Agent API Token')
    parser.add_argument('--server', help='Manager Server URL')
    parser.add_argument('--name', help='Agent Hostname')
    
    args = parser.parse_args()

    if args.install:
        if not args.token or not args.server or not args.name:
            print("❌ Error: --token, --server, and --name are required for installation.")
            sys.exit(1)
        install_agent(args.server, args.token, args.name)
    else:
        # Runtime Mode
        cfg = load_config()
        if not cfg.get('AGENT_TOKEN'):
            print("❌ Config missing. Run with --install first.")
            sys.exit(1)
            
        logging.info(f"Agent Started: {cfg['AGENT_HOSTNAME']}")
        while True:
            heartbeat(cfg)
            time.sleep(cfg['POLL_INTERVAL'])
