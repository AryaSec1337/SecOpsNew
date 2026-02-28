
import sys
import subprocess
import os

print(f"Installing yara-python for: {sys.executable}")
subprocess.check_call([sys.executable, "-m", "pip", "install", "yara-python", "--user"])

try:
    import yara
    print("SUCCESS: yara module imported.")
    print(f"Location: {yara.__file__}")
except ImportError as e:
    print(f"FAILURE: {e}")
    
    # Try to find where it went
    import site
    print(f"User site: {site.getusersitepackages()}")
    print("sys.path:")
    for p in sys.path:
        print(p)
