<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\YarGenHistory;
use Illuminate\Support\Facades\Auth;

class YarGenController extends Controller
{
    public function index()
    {
        $history = YarGenHistory::where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();
            
        return view('investigation.yargen.index', compact('history'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|max:10240', // Max 10MB per file
            'author' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        // Create a unique temporary directory
        $sessionId = Str::uuid()->toString();
        $tempDir = storage_path("app/yargen/{$sessionId}");
        $samplesDir = "{$tempDir}/samples";
        
        if (!file_exists($samplesDir)) {
            mkdir($samplesDir, 0755, true);
        }

        try {
            // Save uploaded files
                $fileNames = [];
                foreach ($request->file('files') as $file) {
                    $name = $file->getClientOriginalName();
                    $fileNames[] = $name;
                    $file->move($samplesDir, $name);
                }

            // Prepare Command
            $yarGenScript = base_path('tools/yarGen/yarGen.py');
            $outputFile = "{$tempDir}/rules.yar";
            $author = $request->input('author', 'SecOps Analyst');
            $reference = $request->input('reference', 'Internal Investigation');
            
            // Command: python yarGen.py -m [samples] -o [output] --opcodes -a [author] -r [ref]
            // Note: Ensure 'python' is in PATH or use specific path like /usr/bin/python3
            $command = [
                config('app.python_path', 'python'), 
                $yarGenScript, 
                '-m', $samplesDir, 
                '-o', $outputFile, 
                '--opcodes', // Enable opcode analysis
                '-a', $author,
                '-r', $reference
            ];

            $yarGenDir = base_path('tools/yarGen');
            $process = new Process($command, $yarGenDir);
            $process->setTimeout(300); // 5 minutes timeout
            $process->run();

            // check if successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            if (file_exists($outputFile)) {
                $rules = file_get_contents($outputFile);
                
                // Save to History
                $history = YarGenHistory::create([
                    'user_id' => Auth::id(),
                    'author' => $author,
                    'reference' => $reference,
                    // 'file_names' => $fileNames, // We need to ensure $fileNames is available here.
                    // Wait, $fileNames is inside the try block scope, but variable scope in PHP... 
                    // variables defined in try block ARE available outside/later in the function.
                    'file_names' => $fileNames ?? [], 
                    'rule_content' => $rules
                ]);

                // Save to Active Rules Directory for Scanner
                $ruleFileName = 'rule_' . $history->id . '_' . Str::slug($reference) . '.yar';
                Storage::put("yara_rules/{$ruleFileName}", $rules);

                return response()->json([
                    'status' => 'success',
                    'rules' => $rules,
                    'history_item' => $history, // Return new history item to prepend to UI
                    'log' => $process->getOutput() 
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rule file was not generated.',
                    'log' => $process->getErrorOutput()
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        } finally {
            // Cleanup
            $this->cleanupDir($tempDir);
        }
    }

    private function cleanupDir($dir) {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->cleanupDir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
