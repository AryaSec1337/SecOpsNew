import sys
import time
import json
import socket
import platform
import argparse
import threading
import subprocess
import os
import re
import xml.etree.ElementTree as ET

# --- Dependency Check ---
def install(package):
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", package])
    except subprocess.CalledProcessError:
        print(f"[!] Standard install failed. Retrying with --break-system-packages...")
        try:
            subprocess.check_call([sys.executable, "-m", "pip", "install", package, "--break-system-packages"])
        except Exception as e:
            print(f"[!] Failed to install {package}. Please install manually using apt: sudo apt install python3-{package}")
            sys.exit(1)

def check_dependencies():
    required = ['requests', 'psutil', 'watchdog']
    for package in required:
        try:
            __import__(package)
        except ImportError:
            print(f"[+] Installing required package: {package}...")
            install(package)

check_dependencies()

import requests
import psutil
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler

# --- Configuration Manager ---
class Config:
    SERVER_URL = ""
    API_TOKEN = ""
    HOSTNAME = ""
    VT_API_KEY = "" # VirusTotal Key
    LOG_FILES = [] # List of dicts: {'path': '...', 'type': 'syslog'}
    FIM_PATHS = [] # List of paths
    MODSEC_LOG_PATH = "" # Path to modsec_audit.log
    
    CONFIG_FILE = "agent.conf"

    @staticmethod
    def load():
        if not os.path.exists(Config.CONFIG_FILE):
            print(f"[!] Config file {Config.CONFIG_FILE} not found.")
            sys.exit(1)
        
        try:
            tree = ET.parse(Config.CONFIG_FILE)
            root = tree.getroot()
            
            Config.SERVER_URL = root.find('server').text
            Config.API_TOKEN = root.find('token').text
            Config.HOSTNAME = root.find('name').text
            
            vt_node = root.find('vt_api_key')
            Config.VT_API_KEY = vt_node.text if vt_node is not None else ""
            
            Config.LOG_FILES = []
            for lf in root.findall('localfile'):
                Config.LOG_FILES.append({
                    'path': lf.find('path').text,
                    'type': lf.find('type').text if lf.find('type') is not None else 'syslog'
                })
                
            Config.FIM_PATHS = []
            fim_node = root.find('fim')
            if fim_node is not None:
                for path in fim_node.findall('path'):
                    Config.FIM_PATHS.append(path.text)
            
            modsec_node = root.find('modsec_log')
            if modsec_node is not None:
                Config.MODSEC_LOG_PATH = modsec_node.text
            
            print("[+] Configuration loaded successfully.")
            
            # Fetch Remote Config (VT Key)
            Config.fetch_remote_config()
            
        except Exception as e:
            print(f"[!] Error parsing config: {e}")
            sys.exit(1)

    @staticmethod
    def fetch_remote_config():
        try:
            print("[*] Fetching remote configuration from server...")
            url = f"{Config.SERVER_URL.rstrip('/')}/api/agent/config"
            headers = {
                'Accept': 'application/json',
                'X-Agent-Token': Config.API_TOKEN
            }
            resp = requests.get(url, headers=headers, timeout=10)
            if resp.status_code == 200:
                data = resp.json()
                remote_cfg = data.get('config', {})
                
                # Update VT Key if provided
                if remote_cfg.get('vt_api_key'):
                    Config.VT_API_KEY = remote_cfg['vt_api_key']
                    print("[+] VirusTotal API Key received from server.")
                else:
                    print("[!] No VirusTotal Key provided by server.")
            else:
                print(f"[!] Failed to fetch remote config: HTTP {resp.status_code}")
        except Exception as e:
            print(f"[!] Error fetching remote config: {e}")

    @staticmethod
    def generate(server, token, name, logs=None, config_path=None):
        target_file = config_path if config_path else Config.CONFIG_FILE
        
        if logs is None:
            # Default Logs based on OS
            if platform.system() == "Windows":
                 logs = [
                     {'path': 'C:\\xampp\\apache\\logs\\access.log', 'type': 'apache'},
                     {'path': 'C:\\xampp\\apache\\logs\\error.log', 'type': 'apache_error'}
                 ]
            else:
                 logs = [
                     {'path': '/var/log/syslog', 'type': 'syslog'},
                     {'path': '/var/log/auth.log', 'type': 'auth'},
                     {'path': '/var/log/nginx/access.log', 'type': 'nginx'}
                 ]

        root = ET.Element("agent_config")
        
        ET.SubElement(root, "server").text = server
        ET.SubElement(root, "token").text = token
        ET.SubElement(root, "name").text = name
        ET.SubElement(root, "vt_api_key").text = "" # User must fill this
        
        # Logs
        for log in logs:
            lf = ET.SubElement(root, "localfile")
            ET.SubElement(lf, "path").text = log['path']
            ET.SubElement(lf, "type").text = log['type']
            
        # FIM
        fim = ET.SubElement(root, "fim")
        # Default FIM paths
        default_fim = ['/etc/passwd', '/var/www/html'] if platform.system() != "Windows" else ['C:\\Windows\\System32\\drivers\\etc\\hosts', 'C:\\xampp\\htdocs']
        for path in default_fim:
             ET.SubElement(fim, "path").text = path
        
        # ModSecurity Default
        modsec_default = '/var/log/modsec_audit.log' if platform.system() != "Windows" else 'C:\\xampp\\apache\\logs\\modsec_audit.log'
        ET.SubElement(root, "modsec_log").text = modsec_default
             
        tree = ET.ElementTree(root)
        ET.indent(tree, space="    ", level=0)
        tree.write(target_file, encoding="utf-8", xml_declaration=True)
        print(f"[+] Generated {target_file}")

    @staticmethod
    def api_url(endpoint):
        return f"{Config.SERVER_URL.rstrip('/')}/api{endpoint}"

    @staticmethod
    def headers():
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Agent-Token': Config.API_TOKEN
        }

import shutil

# --- Service Installer ---
class Installer:
    @staticmethod
    def get_install_dir():
        if platform.system() == "Windows":
            return "C:\\secops-siem"
        else:
            return "/var/secops-siem"

    @staticmethod
    def prepare_install():
        install_dir = Installer.get_install_dir()
        if not os.path.exists(install_dir):
            try:
                os.makedirs(install_dir)
                print(f"[+] Created install directory: {install_dir}")
            except Exception as e:
                print(f"[!] Failed to create install directory: {e}")
                sys.exit(1)
                
        # Copy Agent Script
        current_script = os.path.abspath(sys.argv[0])
        target_script = os.path.join(install_dir, "agent.py")
        
        # Only copy if we are not already running from the target
        if current_script.lower() != target_script.lower():
            try:
                shutil.copy2(current_script, target_script)
                print(f"[+] Copied agent to: {target_script}")
            except Exception as e:
                print(f"[!] Failed to copy agent script: {e}")
                
        return install_dir, target_script

    @staticmethod
    def install_service(server, token, name):
        install_dir, script_path = Installer.prepare_install()
        
        # Generate Config in the Install Dir
        config_path = os.path.join(install_dir, Config.CONFIG_FILE)
        Config.generate(server, token, name, config_path=config_path)

        system = platform.system()
        cwd = install_dir
        
        if system == "Linux":
            service_content = f"""[Unit]
Description=SecOps Agent Service
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory={cwd}
ExecStart={sys.executable} {script_path}
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
"""
            service_path = "/etc/systemd/system/secops-agent.service"
            try:
                with open(service_path, "w") as f:
                    f.write(service_content)
                
                print("[+] Systemd service created.")
                subprocess.call(["systemctl", "daemon-reload"])
                subprocess.call(["systemctl", "enable", "secops-agent"])
                subprocess.call(["systemctl", "start", "secops-agent"])
                print("[+] Service started successfully!")
                
            except PermissionError:
                print("[!] Need root privileges to install service. Try 'sudo'.")
                
        elif system == "Windows":
            # Using PowerShell to create a Scheduled Task at Startup (Running as SYSTEM)
            # This is more robust for python scripts than 'sc create' which expects a binary service wrapper.
            task_name = "SecOpsAgent"
            cmd = f'powershell -Command "Register-ScheduledTask -Action (New-ScheduledTaskAction -Execute \'{sys.executable}\' -Argument \'{script_path}\' -WorkingDirectory \'{cwd}\') -Trigger (New-ScheduledTaskTrigger -AtStartup) -Principal (New-ScheduledTaskPrincipal -UserId \'SYSTEM\' -LogonType ServiceAccount) -TaskName \'{task_name}\' -Force"'
            
            print(f"[+] Installing Windows Background Task '{task_name}'...")
            try:
                subprocess.check_call(cmd, shell=True)
                print(f"[+] Task registered. Starting now...")
                subprocess.call(f'powershell -Command "Start-ScheduledTask -TaskName \'{task_name}\'"', shell=True)
                print("[+] Agent started in background!")
            except Exception as e:
                 print(f"[!] Failed to install Windows task: {e}")
                 print("Try running as Administrator.")

    @staticmethod
    def uninstall_service():
        system = platform.system()
        print(f"[*] Removing Service ({system})...")
        
        if system == "Linux":
            try:
                subprocess.call(["systemctl", "stop", "secops-agent"])
                subprocess.call(["systemctl", "disable", "secops-agent"])
                os.remove("/etc/systemd/system/secops-agent.service")
                subprocess.call(["systemctl", "daemon-reload"])
                print("[+] Systemd service removed.")
            except Exception as e:
                print(f"[!] Failed to remove service: {e}")
                
        elif system == "Windows":
            task_name = "SecOpsAgent"
            cmd = f'powershell -Command "Unregister-ScheduledTask -TaskName \'{task_name}\' -Confirm:$false"'
            try:
                subprocess.call(cmd, shell=True)
                print("[+] Windows Background Task removed.")
            except Exception as e:
                print(f"[!] Failed to remove task: {e}")

        # Remove Config
        if os.path.exists(Config.CONFIG_FILE):
            os.remove(Config.CONFIG_FILE)
            print("[+] Config file deleted.")
            
        print("[*] Agent Uninstalled successfully. Bye!")



# --- Network Blocker (IP Blocking) ---
class NetworkBlocker:
    RULE_NAME = "SecOps Block List"

    @staticmethod
    def block_ip(ip):
        system = platform.system()
        print(f"[Blocker] Blocking IP: {ip} on {system}")
        
        try:
            if system == "Linux":
                # Check if already blocked
                check_cmd = ["iptables", "-C", "INPUT", "-s", ip, "-j", "DROP"]
                if subprocess.call(check_cmd, stderr=subprocess.DEVNULL) != 0:
                    subprocess.check_call(["iptables", "-A", "INPUT", "-s", ip, "-j", "DROP"])
                    print(f"[+] IP {ip} blocked via iptables.")
                else:
                     print(f"[*] IP {ip} already blocked.")
                return True
                
            elif system == "Windows":
                # 1. Get current RemoteAddress list from existing rule
                cmd_get = f'powershell -Command "(Get-NetFirewallRule -DisplayName \'{NetworkBlocker.RULE_NAME}\' -ErrorAction SilentlyContinue | Get-NetFirewallAddressFilter).RemoteAddress"'
                try:
                    output = subprocess.check_output(cmd_get, shell=True).decode().strip()
                    current_ips = [x.strip() for x in output.splitlines() if x.strip()]
                except subprocess.CalledProcessError:
                    current_ips = []

                if ip in current_ips:
                    print(f"[*] IP {ip} already in block list.")
                    return True
                
                # 2. Add new IP
                new_ips = current_ips + [ip]
                ip_list_str = ",".join(new_ips)
                
                # 3. Create or Set Rule
                # Check if rule exists base (not just filter)
                check_rule = f'powershell -Command "Get-NetFirewallRule -DisplayName \'{NetworkBlocker.RULE_NAME}\' -ErrorAction SilentlyContinue"'
                if subprocess.call(check_rule, shell=True) != 0:
                    # Create New
                    print(f"[+] Creating new rule '{NetworkBlocker.RULE_NAME}' with IP {ip}")
                    cmd = f'powershell -Command "New-NetFirewallRule -DisplayName \'{NetworkBlocker.RULE_NAME}\' -Direction Inbound -LocalPort Any -Protocol Any -Action Block -RemoteAddress {ip}"'
                    subprocess.check_call(cmd, shell=True)
                else:
                    # Update Existing
                    print(f"[+] Updating rule '{NetworkBlocker.RULE_NAME}', adding {ip}")
                    cmd = f'powershell -Command "Set-NetFirewallRule -DisplayName \'{NetworkBlocker.RULE_NAME}\' -RemoteAddress {ip_list_str}"'
                    subprocess.check_call(cmd, shell=True)
                    
                return True
                
        except Exception as e:
            print(f"[!] Block Failed: {e}")
            return False, str(e)
            
        return False, "Unsupported OS"

    @staticmethod
    def unblock_ip(ip):
        system = platform.system()
        print(f"[Blocker] Unblocking IP: {ip} on {system}")
        
        try:
             if system == "Linux":
                subprocess.check_call(["iptables", "-D", "INPUT", "-s", ip, "-j", "DROP"])
                print(f"[+] IP {ip} unblocked via iptables.")
                return True
                
             elif system == "Windows":
                # 1. Get current IPs
                cmd_get = f'powershell -Command "(Get-NetFirewallRule -DisplayName \'{NetworkBlocker.RULE_NAME}\' -ErrorAction SilentlyContinue | Get-NetFirewallAddressFilter).RemoteAddress"'
                try:
                    output = subprocess.check_output(cmd_get, shell=True).decode().strip()
                    current_ips = [x.strip() for x in output.splitlines() if x.strip()]
                except:
                    current_ips = []

                if ip not in current_ips:
                    print(f"[*] IP {ip} not found in block list.")
                    return True

                # 2. Remove IP
                new_ips = [x for x in current_ips if x != ip]
                
                if not new_ips:
                    # Use a dummy loopback or delete rule? Better to keep rule but empty? 
                    # Set-NetFirewallRule doesnt accept empty. Let's delete if empty logic or put a placeholder?
                    # Deleting is cleaner.
                    print(f"[+] Block list empty. Removing rule '{NetworkBlocker.RULE_NAME}'")
                    cmd = f'powershell -Command "Remove-NetFirewallRule -DisplayName \'{NetworkBlocker.RULE_NAME}\' -Confirm:$false"'
                    subprocess.check_call(cmd, shell=True)
                else:
                    ip_list_str = ",".join(new_ips)
                    print(f"[+] Updating rule '{NetworkBlocker.RULE_NAME}', removing {ip}")
                    cmd = f'powershell -Command "Set-NetFirewallRule -DisplayName \'{NetworkBlocker.RULE_NAME}\' -RemoteAddress {ip_list_str}"'
                    subprocess.check_call(cmd, shell=True)

                return True
                
        except Exception as e:
            print(f"[!] Unblock Failed: {e}")
            return False, str(e)

# --- Heartbeat Module ---
def get_ip_address():
    try:
        # Determine Host and Port from Config.SERVER_URL
        # Remove protocol
        url_part = Config.SERVER_URL.split('//')[-1]
        
        if ':' in url_part:
            host, port_str = url_part.split(':')
            port = int(port_str.split('/')[0]) # Handle trailing slash
        else:
            host = url_part.split('/')[0]
            port = 80

        # Connect to server to determine outbound IP (UDP is connectionless, but connect fails if route invalid)
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect((host, port))
        ip = s.getsockname()[0]
        s.close()
        return ip
    except Exception:
        return socket.gethostbyname(socket.gethostname())

def get_size(bytes, suffix="B"):
    factor = 1024
    for unit in ["", "K", "M", "G", "T", "P"]:
        if bytes < factor:
            return f"{bytes:.2f} {unit}{suffix}"
        bytes /= factor

def send_heartbeat():
    while True:
        try:
            mem = psutil.virtual_memory()
            disk = psutil.disk_usage('/')
            
            data = {
                "hostname": Config.HOSTNAME,
                "ip_address": get_ip_address(),
                "type": "server",
                "os": f"{platform.system()} {platform.release()}",
                "os_version": platform.version(),
                "role": "Agent",
                "status": "Online",
                "location": "Unknown",
                "metadata": {
                    "cpu_percent": psutil.cpu_percent(),
                    "memory_percent": mem.percent,
                    "memory_total": get_size(mem.total),
                    "memory_used": get_size(mem.used),
                    "disk_percent": disk.percent,
                    "disk_total": get_size(disk.total),
                    "disk_used": get_size(disk.used),
                }
            }
            
            response = requests.post(Config.api_url('/heartbeat'), json=data, headers=Config.headers())
            
            # --- Self-Destruct Mechanism ---
            if response.status_code == 401:
                print("[!] 401 Unauthorized - Agent Token Invalid/Deleted.")
                print("[!] Initiating Self-Uninstallation...")
                Installer.uninstall_service()
                sys.exit(0)
            
            # --- Process Actions (IP Blocking) ---
            if response.status_code == 200:
                resp_json = response.json()
                actions = resp_json.get('actions', [])
                
                for action in actions:
                    action_type = action.get('type')
                    target_ip = action.get('ip')
                    db_id = action.get('id')
                    
                    if not target_ip or not db_id: continue
                    
                    success = False
                    error_msg = None
                    
                    if action_type == 'block_ip':
                        res = NetworkBlocker.block_ip(target_ip)
                        if isinstance(res, bool): success = res
                        else: success, error_msg = res
                            
                    elif action_type == 'unblock_ip':
                        res = NetworkBlocker.unblock_ip(target_ip)
                        if isinstance(res, bool): success = res
                        else: success, error_msg = res
                    
                    # Send ACK
                    ack_status = 'blocked' if (action_type == 'block_ip' and success) else \
                                 'unblocked' if (action_type == 'unblock_ip' and success) else \
                                 'failed'
                                 
                    try:
                        ack_payload = {'id': db_id, 'status': ack_status}
                        if error_msg: ack_payload['error'] = str(error_msg)
                        
                        requests.post(Config.api_url('/block-ip/ack'), json=ack_payload, headers=Config.headers())
                        print(f"[Blocker] ACK Sent: {ack_status}")
                    except Exception as e:
                        print(f"[!] ACK Failed: {e}")

        except Exception as e:
            print(f"[!] Heartbeat Error: {e}")
        
        time.sleep(10)

# --- ModSecurity Monitor ---
class ModSecFollower(threading.Thread):
    def __init__(self):
        threading.Thread.__init__(self)
        self.filepath = Config.MODSEC_LOG_PATH
        self.daemon = True
        # Buffer for Serial Format: { 'unique_id': { 'A': [], 'B': [], 'H': [] } }
        self.buffer = {} 
        self.current_id = None
        self.current_section = None
        # Metric Log format usually: ---ID---SECTION--
        self.sep_pattern = re.compile(r'^---([a-zA-Z0-9]+)---([A-Z])--$')

    def run(self):
        if not self.filepath or not os.path.exists(self.filepath):
            if self.filepath: print(f"[!] ModSecurity Log not found: {self.filepath}")
            return

        print(f"[+] Monitoring ModSecurity Audit Log: {self.filepath}")
        
        try:
            f = open(self.filepath, 'r')
            # Seek to end to monitor new logs only
            f.seek(0, os.SEEK_END)
        except Exception as e:
            print(f"[!] Error opening ModSec log {self.filepath}: {e}")
            return
        
        while True:
            line = f.readline()
            if not line:
                time.sleep(0.5)
                continue
            
            line = line.strip()
            
            # Check for Section Header: ---ID---SECTION--
            m = self.sep_pattern.match(line)
            if m:
                tx_id = m.group(1)
                section = m.group(2)
                
                if section == 'A':
                    # Start of Transaction
                    self.buffer[tx_id] = {}
                    self.current_id = tx_id
                    self.current_section = 'A'
                
                elif section == 'Z':
                    # End of Transaction -> Process
                    if tx_id in self.buffer:
                        self.process_buffer(tx_id, self.buffer[tx_id])
                        del self.buffer[tx_id] # Clear memory
                    self.current_id = None
                    self.current_section = None
                
                else:
                    # Switch Section
                    self.current_id = tx_id
                    self.current_section = section
                
                continue

            # Capture Content
            if self.current_id and self.current_section:
                # Ensure dict structure
                if self.current_id not in self.buffer:
                    self.buffer[self.current_id] = {}
                if self.current_section not in self.buffer[self.current_id]:
                    self.buffer[self.current_id][self.current_section] = []
                
                self.buffer[self.current_id][self.current_section].append(line)

    def process_buffer(self, serial_id, parts):
        try:
            # 1. Parse Section A (Metadata)
            # Format: [Date] Unique_ID SrcIP SrcPort DstIP DstPort
            # Ex: [03/Feb/2026:23:36:30 +0000] 177016179089.194751 ::1 44280 ::1 80
            unique_id = serial_id
            client_ip = "0.0.0.0"
            if 'A' in parts and parts['A']:
                line_a = parts['A'][0]
                # Regex to extract items
                # Try simple split first (space delimited)
                tokens = line_a.split()
                if len(tokens) >= 4:
                    # tokens[0]+[1] date
                    unique_id = tokens[2]
                    client_ip = tokens[3]

            # 2. Parse Section B (Request Headers)
            # Line 1: METHOD URI PROTOCOL
            method = "UNKNOWN"
            uri = "/"
            user_agent = "Unknown"
            host = "Unknown"
            
            if 'B' in parts and parts['B']:
                req_line = parts['B'][0].split()
                if len(req_line) >= 2:
                    method = req_line[0]
                    uri = req_line[1]
                
                # Scan headers
                for header in parts['B'][1:]:
                    if header.lower().startswith('user-agent:'):
                        user_agent = header.split(':', 1)[1].strip()
                    elif header.lower().startswith('host:'):
                        host = header.split(':', 1)[1].strip()

            # 3. Parse Section H (Audit Log / Messages)
            messages = []
            if 'H' in parts:
                for line in parts['H']:
                    # Look for "id" and "msg"
                    # Ex: ModSecurity: Warning. ... [id "942100"] ... [msg "SQL Injection..."] ...
                    if "ModSecurity:" in line:
                        msg_data = {}
                        
                        # Extract Message Text (heuristically, text after Warning/Error/etc until first [)
                        # Easier: extract [msg "..."]
                        msg_match = re.search(r'\[msg "(.*?)"\]', line)
                        msg_data['message'] = msg_match.group(1) if msg_match else "ModSecurity Alert"
                        
                        # Details
                        details = {}
                        id_match = re.search(r'\[id "(.*?)"\]', line)
                        details['ruleId'] = id_match.group(1) if id_match else "0"
                        
                        sev_match = re.search(r'\[severity "(.*?)"\]', line)
                        details['severity'] = sev_match.group(1) if sev_match else "0"
                        
                        data_match = re.search(r'\[data "(.*?)"\]', line)
                        details['data'] = data_match.group(1) if data_match else ""
                        
                        msg_data['details'] = details
                        messages.append(msg_data)

            # Construct JSON Payload compliant with backend ModSecurityController
            payload = {
                "transaction": {
                    "client_ip": client_ip,
                    "time_stamp": time.strftime('%Y-%m-%d %H:%M:%S'),
                    "unique_id": unique_id,
                    "request": {
                        "method": method,
                        "uri": uri,
                        "headers": {
                            "Host": host,
                            "User-Agent": user_agent
                        }
                    },
                    "messages": messages
                }
            }
            
            # Send if messages exist (or always send if configured to log all)
            if messages:
                print(f"[ModSec] Sending Alert: {unique_id} ({len(messages)} rules)")
                requests.post(Config.api_url('/modsecurity'), json=payload, headers=Config.headers())

        except Exception as e:
            print(f"[!] Error Parsing Serial Log: {e}")

# --- Log Monitoring Module ---
class LogFileFollower(threading.Thread):
    def __init__(self, log_config):
        threading.Thread.__init__(self)
        self.filepath = log_config['path']
        self.log_type = log_config['type']
        self.daemon = True
        # Regex for Common Log Format / Combined (Nginx/Apache)
        # matches: IP - - [Date] "METHOD Path Protocol" Status Size "Referer" "UserAgent"
        self.log_pattern = re.compile(r'^(\S+) \S+ \S+ \[(.*?)\] "(.*?) (.*?) .*?" (\d{3}) (\S+)(?: "(.*?)" "(.*?)")?')

    def parse_log(self, line):
        data = {}
        if self.log_type in ['nginx', 'apache', 'apache_error']:
            match = self.log_pattern.search(line)
            if match:
                data['client_ip'] = match.group(1)
                # group 2 is date
                data['method'] = match.group(3)
                data['path'] = match.group(4)
                data['status_code'] = match.group(5)
                data['size'] = match.group(6) if match.group(6).isdigit() else 0
                if len(match.groups()) > 7:
                    data['user_agent'] = match.group(8)
                    
                # Basic OS detection from UA
                if 'Windows' in str(data.get('user_agent', '')):
                    data['os'] = 'Windows'
                elif 'Linux' in str(data.get('user_agent', '')):
                    data['os'] = 'Linux'
                elif 'Mac' in str(data.get('user_agent', '')):
                    data['os'] = 'MacOS'
                else:
                    data['os'] = 'Other'
                    
                print(f"[DEBUG] Parsed Log: {data['method']} {data['path']} from {data['client_ip']}")
        return data

    def run(self):
        if not os.path.exists(self.filepath):
            print(f"[!] Log file not found: {self.filepath}")
            return

        print(f"[+] Monitoring Log ({self.log_type}): {self.filepath}")
        
        try:
            f = open(self.filepath, 'r')
            f.seek(0, os.SEEK_END)
        except Exception as e:
            print(f"[!] Error opening log file {self.filepath}: {e}")
            return
        
        while True:
            line = f.readline()
            if not line:
                time.sleep(0.5)
                continue
                
            log_line = line.strip()
            if not log_line: continue
            
            # Basic Payload
            payload = {
                "timestamp": time.strftime('%Y-%m-%d %H:%M:%S'),
                "log_file": self.filepath,
                "agent_name": Config.HOSTNAME,
                "agent_ip": get_ip_address(), # Use robust IP detection
                "log_raw": log_line,
                "app_type": self.log_type
            }
            
            # Parse Details
            parsed = self.parse_log(log_line)
            payload.update(parsed)
            
            try:
                requests.post(Config.api_url('/logs'), json=payload, headers=Config.headers())
            except Exception as e:
                # Silent fail to avoid spam, or print simple error
                # print(f"[!] Log Send Error: {e}")
                pass

import hashlib
import base64

# --- VirusTotal & Quarantine Logic ---
class VirusTotalModule:
    BASE_URL = "https://www.virustotal.com/api/v3"

    @staticmethod
    def calculate_hash(filepath):
        sha256_hash = hashlib.sha256()
        try:
            with open(filepath, "rb") as f:
                for byte_block in iter(lambda: f.read(4096), b""):
                    sha256_hash.update(byte_block)
            return sha256_hash.hexdigest()
        except:
            return None

    @staticmethod
    def check_server_cache(file_hash):
        try:
            url = Config.api_url('/fim/check-hash')
            payload = {'hash': file_hash}
            resp = requests.post(url, json=payload, headers=Config.headers(), timeout=5)
            if resp.status_code == 200:
                data = resp.json()
                if data.get('status') == 'found':
                    return data.get('virustotal')
        except Exception as e:
            # print(f"[!] Info: Cache check failed: {e}") 
            pass
        return None

    @staticmethod
    def check_file(filepath):
        if not Config.VT_API_KEY:
            return None

        file_hash = VirusTotalModule.calculate_hash(filepath)
        if not file_hash: return None

        print(f"[VT] Checking Hash: {file_hash}")
        
        # 0. Check Server Cache First
        cached = VirusTotalModule.check_server_cache(file_hash)
        if cached:
            print(f"[VT] Found cached result on server.")
            return cached

        headers = {'x-apikey': Config.VT_API_KEY}
        
        # 1. Check Hash (VirusTotal API)
        try:
            resp = requests.get(f"{VirusTotalModule.BASE_URL}/files/{file_hash}", headers=headers)
            if resp.status_code == 200:
                print(f"[VT] File found in VT database.")
                return VirusTotalModule.parse_response(resp.json(), file_hash)
            elif resp.status_code == 404:
                print(f"[VT] Hash not found. Uploading file...")
                return VirusTotalModule.upload_and_scan(filepath, headers, file_hash)
        except Exception as e:
            print(f"[!] VT API Error: {e}")
            return None
            
        return {'hash': file_hash, 'status': 'error'}

    @staticmethod
    def upload_and_scan(filepath, headers, original_hash):
        # Limit file size to 32MB for standard API
        if os.path.getsize(filepath) > 32 * 1024 * 1024:
             return {'hash': original_hash, 'status': 'skipped_size'}

        try:
            with open(filepath, 'rb') as f:
                files = {'file': (os.path.basename(filepath), f)}
                print(f"[VT] Uploading {os.path.basename(filepath)}...")
                resp = requests.post(f"{VirusTotalModule.BASE_URL}/files", headers=headers, files=files)
                
                if resp.status_code == 200:
                    data = resp.json()['data']
                    id_b64 = data['id']
                    
                    # Decode Base64 ID to get the handle/hash
                    # ID format: Base64(Hash:Timestamp) -> e.g "hash:12345"
                    try:
                        decoded = base64.urlsafe_b64decode(id_b64).decode('utf-8')
                        # The user wants the first part before any colon (or just the hash)
                        file_identifier = decoded.split(':')[0]
                        print(f"[VT] Upload Success. Identifier: {file_identifier}")
                        
                        # Now Scan/Check this identifier
                        print(f"[VT] Querying report for: {file_identifier}")
                        # Small delay to ensure backend registration (optional, but good practice)
                        time.sleep(2) 
                        
                        scan_resp = requests.get(f"{VirusTotalModule.BASE_URL}/files/{file_identifier}", headers=headers)
                        if scan_resp.status_code == 200:
                             return VirusTotalModule.parse_response(scan_resp.json(), original_hash)
                        else:
                             print(f"[!] VT Report Fetch Failed: {scan_resp.status_code} - {scan_resp.text}")
                             # Fallback to just returning uploaded status if report not ready
                             return {'hash': original_hash, 'status': 'uploaded', 'analysis_id': id_b64}
                             
                    except Exception as e:
                        print(f"[!] ID Decode Error: {e}")
                        
                else:
                    print(f"[!] VT Upload Failed: {resp.status_code} - {resp.text}")
                    
        except Exception as e:
            print(f"[!] VT Upload/Scan Error: {e}")
        
        return {'hash': original_hash, 'status': 'upload_failed'}

    @staticmethod
    def parse_response(json_data, file_hash):
        try:
            attrs = json_data['data']['attributes']
            stats = attrs.get('last_analysis_stats', {})
            return {
                'hash': file_hash,
                'stats': stats,
                'status': 'analyzed',
                'full_report': json_data # Include full report for details
            }
        except:
             return {'hash': file_hash, 'status': 'parse_error'}

    @staticmethod
    def quarantine_file(filepath):
        quarantine_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "quarantine")
        if not os.path.exists(quarantine_dir):
            os.makedirs(quarantine_dir)
            
        filename = os.path.basename(filepath)
        dest = os.path.join(quarantine_dir, f"{filename}_{int(time.time())}.infected")
        
        try:
            # Retry mechanism for locked files
            max_retries = 3
            for i in range(max_retries):
                try:
                    shutil.move(filepath, dest)
                    print(f"[!!!] QUARANTINED: {filepath} -> {dest}")
                    return True, dest
                except PermissionError:
                    time.sleep(1)
            return False, "Permission Denied"
        except Exception as e:
            return False, str(e)

# --- File Integrity Monitoring (FIM) ---
class FIMHandler(FileSystemEventHandler):
    def on_modified(self, event):
        if event.is_directory: return
        self.process_event(event.src_path, 'Modified')

    def on_created(self, event):
        if event.is_directory: return
        self.process_event(event.src_path, 'Created')

    def on_deleted(self, event):
        if event.is_directory: return
        self.send_alert(event.src_path, 'Deleted', {})

    def process_event(self, path, change_type):
        details = {}
        severity = "Medium"
        
        # 1. Gather File Metadata
        try:
            if os.path.exists(path):
                stats = os.stat(path)
                details['size'] = stats.st_size
                # Get octal permissions (e.g., 0o644)
                details['permissions'] = oct(stats.st_mode)[-3:]
                details['last_modified'] = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(stats.st_mtime))
                
                # MIME Type Guessing
                import mimetypes
                mime, _ = mimetypes.guess_type(path)
                details['mime_type'] = mime or 'Unknown'
        except Exception as e:
            print(f"[!] Error getting stats for {path}: {e}")
            
        # 2. VirusTotal Check
        vt_result = VirusTotalModule.check_file(path)
        if vt_result:
            details['virustotal'] = vt_result
            details['hash'] = vt_result.get('hash')
            
            # Decision Logic
            if vt_result.get('stats', {}).get('malicious', 0) > 0:
                print(f"[!!!] MALWARE DETECTED in {path}")
                severity = "Critical"
                
                # Auto Quarantine
                success, note = VirusTotalModule.quarantine_file(path)
                details['quarantine'] = {
                    'status': success,
                    'location': note if success else None,
                    'error': note if not success else None
                }
                if success:
                    change_type = "Quarantined"

        self.send_alert(path, change_type, details, severity)

    def send_alert(self, path, change_type, details, severity="Medium"):
        print(f"[FIM] {change_type}: {path}")
        payload = {
            "file_path": path,
            "change_type": change_type,
            "detected_at": time.strftime('%Y-%m-%d %H:%M:%S'),
            "agent_name": Config.HOSTNAME,
            "severity": severity,
            "details": details,
            "hash_after": details.get('hash') # Explicitly map for DB column
        }
        try:
            requests.post(Config.api_url('/fim'), json=payload, headers=Config.headers())
        except Exception as e:
            print(f"[!] FIM Send Error: .") # Keep silent to avoid chaos loop

def start_fim(paths):
    observer = Observer()
    handler = FIMHandler()
    dirs_to_watch = set()
    
    for path in paths:
        # If file, watch directory
        if os.path.isfile(path):
            dirs_to_watch.add(os.path.dirname(path))
        elif os.path.isdir(path):
            dirs_to_watch.add(path)
            
    for d in dirs_to_watch:
        if os.path.exists(d):
            print(f"[+] FIM Watching: {d} (Recursive)")
            observer.schedule(handler, d, recursive=True)
            
    observer.start()
    return observer

# --- Main Entry Point ---
if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='SecOps Agent')
    parser.add_argument('--install', action='store_true', help='Install agent as service')
    parser.add_argument('--uninstall', action='store_true', help='Uninstall agent service and config')
    parser.add_argument('--token', help='Agent Access Token (Install Mode)')
    parser.add_argument('--server', help='Manager URL (Install Mode)')
    parser.add_argument('--name', help='Agent Hostname (Install Mode)')

    args = parser.parse_args()

    if args.uninstall:
        Installer.uninstall_service()
        sys.exit(0)

    if args.install:
        if not args.token or not args.server or not args.name:
            print("[!] Install mode requires --token, --server, and --name")
            sys.exit(1)
            
        print("[*] Installing Service...")
        # Note: Config generation moved inside install_service to support target directory
        Installer.install_service(args.server, args.token, args.name)
        sys.exit(0)

    # Standard Run Mode (Load Config)
    Config.load()

    print(f"[*] Starting Agent: {Config.HOSTNAME}")
    print(f"[*] Connecting to: {Config.SERVER_URL}")

    # 1. Start Heartbeat Thread
    hb_thread = threading.Thread(target=send_heartbeat, daemon=True)
    hb_thread.start()

    # 2. Start Log Monitors
    for log_config in Config.LOG_FILES:
        t = LogFileFollower(log_config)
        t.start()

    # 3. Start FIM
    fim_observer = start_fim(Config.FIM_PATHS)

    # 4. Start ModSecurity Monitor
    if Config.MODSEC_LOG_PATH:
        ms_thread = ModSecFollower()
        ms_thread.start()

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        if fim_observer:
            fim_observer.stop()
            fim_observer.join()
        print("\n[!] Agent Stopped.")
