<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Backup;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function download($id)
    {
        $backup = Backup::findOrFail($id);

        if (!Storage::exists($backup->path)) {
            return redirect()->back()->with('error', 'File backup not found on server.');
        }

        return Storage::download($backup->path, $backup->filename);
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);

        if (Storage::exists($backup->path)) {
            Storage::delete($backup->path);
        }

        $backup->delete();

        return redirect()->back()->with('success', 'Backup deleted successfully.');
    }
}
