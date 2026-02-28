<?php

namespace App\Http\Controllers;

use App\Models\EmailsUser;
use Illuminate\Http\Request;

class AssetEmailController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailsUser::query();

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('display_name', 'LIKE', "%{$search}%")
                  ->orWhere('email_address', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }

        if ($dept = $request->get('department')) {
            $query->where('department', $dept);
        }

        $emails = $query->orderBy('display_name')->paginate(20)->withQueryString();

        $departments = EmailsUser::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        $totalCount = EmailsUser::count();

        return view('assets.email.index', compact('emails', 'departments', 'totalCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'email_address' => 'required|email|max:255|unique:emails_users,email_address',
            'recipient_type' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        EmailsUser::create($validated);

        return back()->with('success', 'Email user created successfully.');
    }

    public function update(Request $request, $id)
    {
        $email = EmailsUser::findOrFail($id);

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'email_address' => 'required|email|max:255|unique:emails_users,email_address,' . $id,
            'recipient_type' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $email->update($validated);

        return back()->with('success', 'Email user updated successfully.');
    }

    public function destroy($id)
    {
        $email = EmailsUser::findOrFail($id);
        $email->delete();

        return back()->with('success', 'Email user deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        // Save temp file
        $tempPath = $file->storeAs('temp', 'import_emails.' . $extension);
        $fullPath = storage_path('app/' . $tempPath);

        try {
            if (in_array($extension, ['xlsx', 'xls'])) {
                $result = $this->importXlsx($fullPath);
            } else {
                $result = $this->importCsv($fullPath);
            }

            // Cleanup
            @unlink($fullPath);

            return back()->with('success', "Import complete! {$result['created']} created, {$result['updated']} updated, {$result['skipped']} skipped.");
        } catch (\Exception $e) {
            @unlink($fullPath);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function importXlsx(string $path): array
    {
        // Use Python to convert xlsx to CSV, then import
        $csvPath = str_replace('.xlsx', '.csv', $path);
        $csvPath = str_replace('.xls', '.csv', $csvPath);

        $pythonScript = <<<PYTHON
import openpyxl, csv, sys
wb = openpyxl.load_workbook(sys.argv[1])
ws = wb.active
with open(sys.argv[2], 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    for row in ws.iter_rows(values_only=True):
        writer.writerow(row)
PYTHON;

        $scriptPath = storage_path('app/temp/convert_xlsx.py');
        file_put_contents($scriptPath, $pythonScript);

        $escapedPath = escapeshellarg($path);
        $escapedCsv = escapeshellarg($csvPath);
        $escapedScript = escapeshellarg($scriptPath);

        exec("python {$escapedScript} {$escapedPath} {$escapedCsv} 2>&1", $output, $exitCode);

        @unlink($scriptPath);

        if ($exitCode !== 0 || !file_exists($csvPath)) {
            throw new \Exception('Failed to convert Excel file. Python output: ' . implode("\n", $output));
        }

        $result = $this->importCsv($csvPath);
        @unlink($csvPath);

        return $result;
    }

    private function importCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \Exception('Could not open file.');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new \Exception('Empty file or invalid format.');
        }

        // Normalize headers
        $headers = array_map(function($h) {
            return strtolower(trim(str_replace([' ', '_'], '_', $h)));
        }, $headers);

        // Map columns
        $emailCol = $this->findColumn($headers, ['email_address', 'email', 'emailaddress', 'mail']);
        $nameCol = $this->findColumn($headers, ['display_name', 'displayname', 'name', 'full_name', 'fullname']);
        $typeCol = $this->findColumn($headers, ['recipient_type', 'recipienttype', 'type', 'mailbox_type']);
        $deptCol = $this->findColumn($headers, ['department', 'dept', 'departemen']);

        if ($emailCol === null) {
            throw new \Exception('Could not find email column. Headers found: ' . implode(', ', $headers));
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $email = trim($row[$emailCol] ?? '');
            if (empty($email) || $email === 'None') {
                $skipped++;
                continue;
            }

            $data = [
                'display_name' => $nameCol !== null ? trim($row[$nameCol] ?? '') : '',
                'recipient_type' => $typeCol !== null ? trim($row[$typeCol] ?? '') : null,
                'department' => $deptCol !== null ? (trim($row[$deptCol] ?? '') ?: null) : null,
            ];

            // Filter out empty strings for non-required fields
            if (empty($data['display_name'])) $data['display_name'] = $email;

            $existing = EmailsUser::where('email_address', $email)->first();

            if ($existing) {
                // Update only non-empty fields
                $updateData = array_filter($data, fn($v) => $v !== null && $v !== '');
                if (!empty($updateData)) {
                    $existing->update($updateData);
                }
                $updated++;
            } else {
                $data['email_address'] = $email;
                EmailsUser::create($data);
                $created++;
            }
        }

        fclose($handle);

        return compact('created', 'updated', 'skipped');
    }

    private function findColumn(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $candidate) {
            $index = array_search($candidate, $headers);
            if ($index !== false) {
                return $index;
            }
        }
        return null;
    }

    public function list(Request $request)
    {
        $query = EmailsUser::query();

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('display_name', 'LIKE', "%{$search}%")
                  ->orWhere('email_address', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('display_name')->limit(50)->get());
    }
}
