import sys
print("Python Executable:", sys.executable)
print("Sys Path:", sys.path)
try:
    import lief
    print("LIEF Version:", lief.__version__)
    print("SUCCESS: lief imported")
except ImportError as e:
    print("ERROR:", e)
