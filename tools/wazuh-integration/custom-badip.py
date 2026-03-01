#!/var/ossec/framework/python/bin/python3
# Wazuh → SecOps Bad IP Alerts integration script

import json
import sys
import time
import os

try:
    import requests
except ImportError:
    print("Module 'requests' not found. Install with: pip install requests")
    sys.exit(1)

# Removed hardcoded ALLOWED_ALERT_PREFIXES

debug_enabled = True
pwd = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))
log_file = f"{pwd}/logs/integrations-badip.log"
if not os.path.exists(log_file):
    os.makedirs(os.path.dirname(log_file), exist_ok=True)
    with open(log_file, "w") as f:
        f.write("=== SecOps Bad IP Integration Log Start ===\n")

now = time.strftime("%a %b %d %H:%M:%S %Z %Y")

def debug(msg):
    if debug_enabled:
        msg = f"{now}: {msg}\n"
        print(msg)
        with open(log_file, "a") as f:
            f.write(msg)

def main(args):
    debug("# Wazuh → SecOps Bad IP script started")

    if len(args) < 3:
        debug("Error: Missing arguments. Expected at least <alert_file> <hook_url>")
        sys.exit(1)

    alert_file = args[1]
    
    # Wazuh passes: script <alert> <apikey> <hook_url>
    hook_url = args[3] if len(args) > 3 else args[2]
    
    debug(f"# Reading alert from: {alert_file}")

    try:
        with open(alert_file, 'r') as f:
            content = f.read().strip()
            if not content:
                debug("Alert file is empty.")
                return
                
            try:
                # Try parsing as a standard JSON object first
                alert = json.loads(content)
            except json.JSONDecodeError:
                # Fallback to reading the last line (Wazuh NDJSON format)
                last_line = content.splitlines()[-1]
                alert = json.loads(last_line)
                
    except Exception as e:
        debug(f"Error reading alert file: {e}")
        sys.exit(1)

    # === PASSTHROUGH LOGIC ===
    rule_desc = alert.get("rule", {}).get("description", "")
    alert_cat = alert.get("data", {}).get("alert", {}).get("category", "")
    
    debug(f"Processng alert: {rule_desc} | Category: {alert_cat}")

    debug("# Sending Bad IP alert to SecOps Webhook")
    send_to_secops(alert, hook_url)

def send_to_secops(payload, hook_url):
    try:
        response = requests.post(
            hook_url,
            headers={"Content-Type": "application/json", "Accept": "application/json"},
            json=payload,
            timeout=10,
            verify=False
        )
        debug(f"SecOps Bad IP response: {response.status_code} - {response.text}")
    except Exception as e:
        debug(f"Error sending to SecOps Bad IP endpoint: {e}")

if __name__ == "__main__":
    try:
        main(sys.argv)
    except Exception as e:
        debug(f"Unhandled exception: {e}")
