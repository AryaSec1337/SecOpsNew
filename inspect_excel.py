import pandas as pd
try:
    df = pd.read_excel('public/emails.xlsx')
    print("Columns:", df.columns.tolist())
    print("First 5 rows:")
    print(df.head().to_string())
except Exception as e:
    print(f"Error reading Excel file: {e}")
