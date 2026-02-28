<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class EmailAnalyzerController extends Controller
{
    public function index()
    {
        return view('investigation.email_analyzer.index');
    }

    public function analyze(Request $request)
    {
        $rawHeaders = $request->input('headers');
        
        if (empty($rawHeaders)) {
            return back()->with('error', 'Please provide email headers.');
        }

        $analysis = $this->parseHeaders($rawHeaders);

        // Save to History
        \App\Models\EmailAnalysisHistory::create([
            'user_id' => auth()->id(),
            'subject' => $analysis['summary']['subject'] ?? 'Unknown Specimen',
            'sender' => $analysis['summary']['from'] ?? 'Unknown',
            'recipient' => $analysis['summary']['to'] ?? 'Unknown',
            'score' => $analysis['risk']['score'] ?? 0,
            'risk_level' => $analysis['risk']['level'] ?? 'Low',
            'results' => $analysis 
        ]);

        return response()->json($analysis);
    }

    public function history()
    {
        $history = \App\Models\EmailAnalysisHistory::where('user_id', auth()->id())
                    ->latest()
                    ->take(50)
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->id,
                            'date' => $item->created_at->diffForHumans(),
                            'subject' => \Illuminate\Support\Str::limit($item->subject, 40),
                            'sender' => \Illuminate\Support\Str::limit($item->sender, 40),
                            'score' => $item->score,
                            'level' => $item->risk_level,
                            'results' => $item->results // Optional: load full results on demand to save bandwidth
                        ];
                    });
                    
        return response()->json($history);
    }
    
    public function showHistory($id)
    {
        $history = \App\Models\EmailAnalysisHistory::where('user_id', auth()->id())->findOrFail($id);
        return response()->json($history->results);
    }

    public function destroyHistory($id)
    {
        \App\Models\EmailAnalysisHistory::where('user_id', auth()->id())->where('id', $id)->delete();
        return response()->json(['message' => 'History deleted']);
    }

    private function parseHeaders($raw)
    {
        $lines = explode("\n", str_replace("\r", "", $raw));
        $headers = [];
        $currentHeader = '';
        
        // Unfold headers
        foreach ($lines as $line) {
            if (preg_match('/^\s+/', $line)) {
                if ($currentHeader) {
                    if (is_array($headers[$currentHeader])) {
                        $lastIndex = count($headers[$currentHeader]) - 1;
                        $headers[$currentHeader][$lastIndex] .= ' ' . trim($line);
                    } else {
                        $headers[$currentHeader] .= ' ' . trim($line);
                    }
                }
            } else {
                if (preg_match('/^([^:]+):(.*)$/', $line, $matches)) {
                    $key = trim($matches[1]);
                    $value = trim($matches[2]);
                    
                    // Handle multiple headers with same name (like Received)
                    if (isset($headers[$key])) {
                        if (!is_array($headers[$key])) {
                            $headers[$key] = [$headers[$key]];
                        }
                        $headers[$key][] = $value;
                    } else {
                        $headers[$key] = $value;
                    }
                    $currentHeader = $key;
                }
            }
        }

        // Parse Hops (Received headers)
        $hops = [];
        $received = $headers['Received'] ?? [];
        if (!is_array($received)) $received = [$received];
        
        // Process in reverse order (bottom to top is usually chronological path from sender to recipient)
        // Index 0 in raw headers is usually the last hop (Local server)
        $received = array_reverse($received);
        
        $prevTime = null;

        foreach ($received as $index => $hopRaw) {
            // Normalize spaces
            $hopRaw = preg_replace('/\s+/', ' ', $hopRaw);

            $hop = [
                'number' => $index + 1,
                'from' => 'Unknown',
                'by' => 'Unknown',
                'with' => 'Unknown',
                'time' => null,
                'delay' => 0,
                'display_delay' => '*',
                'ip' => null
            ];

            // 1. Extract Date (Time joined the chain)
            // Valid formats often end with semicolon + date
            if (preg_match('/;\s*(.+)$/', $hopRaw, $matches)) {
                try {
                    $dateStr = trim($matches[1]);
                    // Clean up some common time zone formats if needed
                    $timestamp = strtotime($dateStr);
                    if ($timestamp) {
                        $hop['time'] = date('Y-m-d H:i:s O', $timestamp);
                        
                        if ($prevTime !== null) {
                            $delay = $timestamp - $prevTime;
                            $hop['delay'] = $delay;
                            $hop['display_delay'] = $this->formatDelay($delay);
                        } else {
                            $hop['display_delay'] = '0s'; // First hop
                        }
                        $prevTime = $timestamp;
                    }
                } catch (\Exception $e) {}
            }

            // 2. Extract "from" - can be "from [ip] (helo)" or "from helo ([ip])"
            // Regex for standard "from ... by ..." pattern
            // Capture "from (something)" until "by" or "via" or "id" or "with"
            if (preg_match('/from\s+(.*?)\s+(by|via|with|id|for|;)/i', $hopRaw, $matches)) {
                $hop['from'] = trim($matches[1]);
            } elseif (preg_match('/from\s+(.*)$/i', $hopRaw, $matches)) {
                 // Fallback if 'by' not present close by
                 $temp = explode(' ', $matches[1]);
                 $hop['from'] = $temp[0]; 
            }
            
            // Extract IP specifically if available in brackets (1.2.3.4) or ([1.2.3.4])
            if (preg_match('/(?:\[|\()(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?:\]|\))/', $hopRaw, $matches)) {
                $hop['ip'] = $matches[1];
            }

            // 3. Extract "by"
            if (preg_match('/by\s+(.*?)\s+(with|via|id|for|;|from)/i', $hopRaw, $matches)) {
                $hop['by'] = trim($matches[1]);
            }

            // 4. Extract "with" (Protocol)
             if (preg_match('/with\s+([A-Za-z0-9]+)/i', $hopRaw, $matches)) {
                $hop['with'] = trim($matches[1]);
            }

            $hops[] = $hop;
        }

        // Security Analysis
        $security = [
            'spf' => $this->extractSecurityHeader($headers, 'Received-SPF'),
            'dkim' => $this->extractSecurityHeader($headers, 'DKIM-Signature'),
            'dmarc' => $this->extractSecurityHeader($headers, 'DMARC-Filter') ?? $this->extractAuthenticationResults($headers, 'dmarc'),
            'auth_results' => $headers['Authentication-Results'] ?? null,
            'arc' => $headers['ARC-Authentication-Results'] ?? null
        ];
        
        // Normalizing Authentication-Results for display (Handle Arrays)
         foreach(['auth_results', 'arc'] as $k) {
            if (isset($security[$k]) && is_array($security[$k])) {
                 $security[$k] = implode("\n", $security[$k]);
             }
         }

        // Basic Info
        $summary = [
            'subject' => $headers['Subject'] ?? '-',
            'from' => $headers['From'] ?? '-',
            'to' => $headers['To'] ?? '-',
            'message_id' => $headers['Message-ID'] ?? '-',
            'return_path' => $headers['Return-Path'] ?? '-',
            'date' => $headers['Date'] ?? '-',
            // X-Mailer is sometimes an array too if multiple headers exist? Rare but possible.
            'x_mailer' => is_array($headers['X-Mailer'] ?? null) ? implode(', ', $headers['X-Mailer']) : ($headers['X-Mailer'] ?? $headers['User-Agent'] ?? '-'),
        ];

        // Phishing Risk Analysis
        $risk = $this->calculateRisk($headers, $security, $hops, $summary, $raw);

        return [
            'summary' => $summary,
            'hops' => $hops,
            'security' => $security,
            'risk' => $risk,
            'raw_parsed' => $headers
        ];
    }

    private function calculateRisk($headers, $security, $hops, $summary, $rawHeaders)
    {
        $score = 0;
        $indicators = [];

        // 1. Authentication Failures (Critical)
        if ($this->isAuthFail($security['spf'])) {
             $score += 25;
             $indicators[] = ['severity' => 'high', 'msg' => 'SPF Validation Failed', 'matches' => 'Result: ' . ($security['spf'] ?? 'None')];
        }
        if ($this->isAuthFail($security['dkim'])) {
             $score += 20;
             $indicators[] = ['severity' => 'medium', 'msg' => 'DKIM Validation Failed', 'matches' => 'Result: ' . ($security['dkim'] ?? 'None')];
        }
        if ($this->isAuthFail($security['dmarc'])) {
             $score += 25; // DMARC fail is usually bad
             $indicators[] = ['severity' => 'high', 'msg' => 'DMARC Validation Failed', 'matches' => 'Result: ' . ($security['dmarc'] ?? 'None')];
        }

        $fromEmail = $this->extractEmail($summary['from']);
        $returnPath = $this->extractEmail($summary['return_path']);
        $replyTo = isset($headers['Reply-To']) ? $this->extractEmail($headers['Reply-To']) : null;

        // 2. Domain Mismatch: Reply-To vs From (High Risk Indicator)
        if ($fromEmail && $replyTo) {
             $fromDomain = substr(strrchr($fromEmail, "@"), 1);
             $replyDomain = substr(strrchr($replyTo, "@"), 1);

             if (strtolower($fromDomain) !== strtolower($replyDomain)) {
                  $score += 25;
                  $indicators[] = [
                      'severity' => 'high', 
                      'msg' => "Reply-To Domain Mismatch",
                      'matches' => "From: $fromEmail | Reply-To: $replyTo"
                  ];
             }
        }

        // 3. Domain Mismatch: Return-Path vs From (Spoofing)
        if ($fromEmail && $returnPath) {
             $fromDomain = substr(strrchr($fromEmail, "@"), 1);
             $returnDomain = substr(strrchr($returnPath, "@"), 1);
             
             // Allow subdomains matching parents
             if ($fromDomain !== $returnDomain && !str_ends_with($fromDomain, '.'.$returnDomain) && !str_ends_with($returnDomain, '.'.$fromDomain)) {
                  $score += 10;
                  $indicators[] = [
                      'severity' => 'medium', 
                      'msg' => "Return-Path Domain Mismatch",
                      'matches' => "From: $fromEmail | Return-Path: $returnPath"
                  ];
             }
        }

        // 4. Hops Analysis (Delays)
        foreach($hops as $hop) {
            if ($hop['delay'] > 3600) { // > 1 hour delay
                 $score += 10;
                 $indicators[] = [
                     'severity' => 'low', 
                     'msg' => "Significant Delay Detected",
                     'matches' => "Hop #{$hop['number']}: " . $this->formatDelay($hop['delay']) . " delay"
                 ];
                 break;
            }
        }

        // 8. Custom Regex Rules
        $rules = \App\Models\EmailAnalyzerRule::where('is_active', true)->get();
        foreach ($rules as $rule) {
            // Check headers, subject, and from against the pattern
            $contentToCheck = $rawHeaders . "\n" . ($summary['subject'] ?? '') . "\n" . ($summary['from'] ?? '');
            
            try {
                if (@preg_match($rule->pattern, $contentToCheck, $matches)) {
                    $score += $rule->score;
                    $matchedText = $matches[0] ?? '';
                    // Trucate long matches
                    if (strlen($matchedText) > 60) $matchedText = substr($matchedText, 0, 60) . '...';
                    
                    $indicators[] = [
                        'severity' => $rule->score >= 20 ? 'high' : 'medium', 
                        'msg' => "Custom Rule: {$rule->name}",
                        'matches' => "Trigger: \"$matchedText\""
                    ];
                }
            } catch (\Exception $e) {
                // Ignore invalid regex to prevent crash
            }
        }

        return [
            'score' => min($score, 100),
            'level' => $score >= 70 ? 'High' : ($score >= 40 ? 'Medium' : 'Low'),
            'indicators' => $indicators
        ];
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pattern' => 'required|string',
            'score' => 'required|integer|min:1|max:100',
        ]);

        // Validate Regex
        if (@preg_match($request->pattern, '') === false) {
             return response()->json(['message' => 'Invalid Regex Pattern'], 422);
        }

        \App\Models\EmailAnalyzerRule::create($request->all());

        return response()->json(['message' => 'Rule created successfully']);
    }

    public function destroyRule($id)
    {
        \App\Models\EmailAnalyzerRule::destroy($id);
        return response()->json(['message' => 'Rule deleted']);
    }

    public function getRules()
    {
        return response()->json(\App\Models\EmailAnalyzerRule::all());
    }

    private function isAuthFail($val) {
        if (!$val) return false; // Unknown is not strictly fail
        return stripos($val, 'fail') !== false || stripos($val, 'softfail') !== false;
    }

    private function extractEmail($str) {
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $str, $matches)) {
            return $matches[0];
        }
        return null;
    }

    private function formatDelay($seconds)
    {
        if ($seconds < 0) return '0s'; // Clock drift handling
        if ($seconds < 60) return $seconds . 's';
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return "{$minutes}m {$remainingSeconds}s";
    }

    private function extractSecurityHeader($headers, $key)
    {
        $val = $headers[$key] ?? null;
        if (is_array($val)) return implode("\n", $val);
        return $val;
    }
    
    private function extractAuthenticationResults($headers, $type) {
         $auth = $headers['Authentication-Results'] ?? null;
         if(!$auth) return null;
         
         if(is_array($auth)) $auth = implode(" ", $auth);
         
         // Simple regex to find dmarc=pass or similar
         if(preg_match('/'.$type.'=([a-zA-Z0-9]+)/i', $auth, $matches)) {
             return $matches[1]; // e.g. "pass"
         }
         return null;
    }
}
