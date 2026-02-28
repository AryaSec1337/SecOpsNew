try:
    import openpyxl
    print("openpyxl is installed")
    wb = openpyxl.load_workbook('public/emails.xlsx')
    sheet = wb.active
    print("Columns:")
    for cell in sheet[1]:
        print(cell.value)
except ImportError:
    print("openpyxl is NOT installed")
except Exception as e:
    print(f"Error: {e}")
