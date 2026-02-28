import time
import requests
import subprocess
import os
import sys
import logging
from dotenv import load_dotenv

# Load Env
load_dotenv()

# Config
MANAGER_URL = os.getenv('MANAGER_URL', 'http://127.0.0.1:8000')
AGENT_TOKEN = os.getenv('AGENT_TOKEN')
HOSTNAME = os.getenv('AGENT_HOSTNAME', 'secops-agent-01')
POLL_INTERVAL = int(os.getenv('POLL_INTERVAL', 5))

# Logging
logging.basicConfig(
    filename='secops_agent.log',
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s'
)

def run_iptables(cmd):
    """Executes an iptables command."""
    try:
        logging.info(f"Executing: {' '.join(cmd)}")
        result = subprocess.run(cmd, capture_output=True, text=True)
        if result.returncode == 0:
            return True, "Executed successfully"
        else:
            return False, result.stderr
    except Exception as e:
        return False, str(e)

def block_ip(ip, port=None, protocol='tcp'):
    """Blocks an IP."""
    cmd = ['iptables', '-I', 'INPUT', '-s', ip, '-j', 'DROP']
    
    if port:
        cmd.extend(['-p', protocol, '--dport', str(port)])
    elif protocol != 'tcp': # If no port but protocol is udp/icmp
        cmd.extend(['-p', protocol])
        
    return run_iptables(cmd)

def unblock_ip(ip, port=None, protocol='tcp'):
    """Unblocks an IP."""
    cmd = ['iptables', '-D', 'INPUT', '-s', ip, '-j', 'DROP']
    
    if port:
        cmd.extend(['-p', protocol, '--dport', str(port)])
    elif protocol != 'tcp':
        cmd.extend(['-p', protocol])
        
    return run_iptables(cmd)

def heartbeat():
    """Polls the manager for commands."""
    url = f"{MANAGER_URL}/api/agent/heartbeat"
    headers = {
        'Authorization': f"Bearer {AGENT_TOKEN}",
        'Accept': 'application/json'
    }
    
    # Optional: Send some metadata
    payload = {
        'metadata': {
            'hostname': HOSTNAME,
            'status': 'active'
        }
    }

    try:
        response = requests.post(url, json=payload, headers=headers, timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            commands = data.get('commands', [])
            
            for cmd in commands:
                process_command(cmd)
        elif response.status_code == 401:
            logging.error("Authentication failed. Check AGENT_TOKEN.")
        else:
            logging.warning(f"Heartbeat failed: {response.status_code} - {response.text}")
            
    except requests.exceptions.ConnectionError:
        logging.error(f"Could not connect to Manager at {MANAGER_URL}")
    except Exception as e:
        logging.error(f"Unexpected error: {e}")

def process_command(cmd):
    """Processes a single command from the manager."""
    cid = cmd['id']
    ctype = cmd['type']
    ip = cmd['ip_address']
    port = cmd.get('port')
    proto = cmd.get('protocol', 'tcp')
    
    logging.info(f"Processing Command #{cid}: {ctype} {ip} ({proto}/{port if port else 'all'})")
    
    success = False
    msg = ""
    
    if ctype == 'block':
        success, msg = block_ip(ip, port, proto)
    elif ctype == 'unblock':
        success, msg = unblock_ip(ip, port, proto)
    else:
        msg = f"Unknown command type: {ctype}"
    
    # Acknowledge
    ack_url = f"{MANAGER_URL}/api/agent/ack"
    ack_payload = {
        'command_id': cid,
        'status': 'success' if success else 'error',
        'message': msg
    }
    
    try:
        requests.post(ack_url, json=ack_payload, headers={'Authorization': f"Bearer {AGENT_TOKEN}"}, timeout=5)
    except Exception as e:
        logging.error(f"Failed to ACK command #{cid}: {e}")

if __name__ == '__main__':
    logging.info("SecOps Agent Starting (Polling Mode)...")
    if not AGENT_TOKEN:
        logging.error("AGENT_TOKEN is missing in .env")
        sys.exit(1)
        
    print(f"Agent started. Polling {MANAGER_URL} every {POLL_INTERVAL}s.")
    
    while True:
        heartbeat()
        time.sleep(POLL_INTERVAL)
