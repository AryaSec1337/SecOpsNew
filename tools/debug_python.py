import sys
import os
import site

print(f"Executable: {sys.executable}")
print(f"CWD: {os.getcwd()}")
print("sys.path:")
for p in sys.path:
    print(f"  {p}")

print("\nUser Site Packages:")
print(site.getusersitepackages())

try:
    import yara
    print("\nSUCCESS: yara module imported.")
    print(f"yara file: {yara.__file__}")
except ImportError as e:
    print(f"\nFAILURE: {e}")

try:
    from pip._internal.operations import freeze
    print("\nInstalled Packages:")
    for req in freeze.freeze():
        print(req)
except Exception:
    print("\nCould not list packages via pip internal.")
