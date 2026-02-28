<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExternalCtiController extends Controller
{
    public function index()
    {
        // 1. Security News (RSS) - Cache for 1 hour
        $news = Cache::remember('cti_news_feed', 3600, function () {
            $feedUrl = "https://feeds.feedburner.com/TheHackersNews";
            try {
                $content = Http::timeout(5)->get($feedUrl)->body();
                $xml = simplexml_load_string($content);
                $json = json_encode($xml);
                $array = json_decode($json, true);
                
                return collect($array['channel']['item'] ?? [])->take(6)->map(function ($item) {
                    $description = $item['description'] ?? '';
                    if (is_array($description)) {
                        $description = '';
                    }

                    return [
                        'title' => $item['title'],
                        'link' => $item['link'],
                        'pubDate' => \Carbon\Carbon::parse($item['pubDate'])->diffForHumans(),
                        'source' => 'The Hacker News',
                        'description' => strip_tags((string)$description)
                    ];
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("RSS Parse Error: " . $e->getMessage());
                return [];
            }
        });

        // 2. Global Ransomware Feed (Ransomwatch) - Fallback
        $victims = Cache::remember('cti_global_ransomwatch_v1', 3600, function () {
            try {
                $response = Http::timeout(10)->get("https://raw.githubusercontent.com/joshhighet/ransomwatch/main/posts.json");
                if ($response->successful()) {
                    $data = $response->json();
                    return collect($data)
                        ->sortByDesc('date')
                        ->take(12)
                        ->map(function ($item) {
                            return [
                                'victim' => $item['post_title'] ?? $item['group_name'],
                                'group' => $item['group_name'],
                                'discovered' => $item['date'],
                                'country' => 'UNK', // Ransomwatch doesn't provide country
                                'description' => null,
                                'screenshot' => null
                            ];
                        });
                }
                return collect([]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Global Feed Error: " . $e->getMessage());
                // Fallback to Mock Data if API fails (Prevent Empty View)
                return collect([
                    [
                        'victim' => 'Lockheed Martin (Simulation)',
                        'group' => 'LockBit3',
                        'discovered' => now()->subHours(2)->toDateTimeString(),
                        'country' => 'US',
                        'description' => 'Mock data: System unable to reach feed provider.',
                        'screenshot' => null
                    ],
                    [
                        'victim' => 'Bundeswehr (Simulation)',
                        'group' => 'BlackBasta',
                        'discovered' => now()->subHours(5)->toDateTimeString(),
                        'country' => 'DE',
                        'description' => 'Mock data: System unable to reach feed provider.',
                        'screenshot' => null
                    ],
                    [
                        'victim' => 'Carrefour Group',
                        'group' => 'Qilin',
                        'discovered' => now()->subDay()->toDateTimeString(),
                        'country' => 'FR',
                        'description' => 'Mock data: System unable to reach feed provider.',
                        'screenshot' => null
                    ]
                ]);
            }
        });
        
        // 3. Top Actors - Fetch from Ransomware.live API
        $topGroups = Cache::remember('cti_top_groups_live', 21600, function () {
            try {
                $apiKey = env('RANSOMWARE_LIVE_API_KEY', 'bd446354-2877-42d9-91e1-23784c3e590e');
                $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'X-API-KEY' => $apiKey
                ])->timeout(10)->get('https://api-pro.ransomware.live/groups');

                if ($response->successful()) {
                    $data = $response->json();
                    return collect($data['groups'] ?? [])
                        ->sortByDesc('victims')
                        ->take(5)
                        ->map(function ($group) {
                            return [
                                'name' => $group['group'],
                                'count' => $group['victims'],
                                'last_seen' => null // API doesn't provide this in group summary
                            ];
                        });
                }
                return [];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Ransomware.live API Error: " . $e->getMessage());
                return [];
            }
        });

        // Fetch Indonesian Victims (Specific for User Request)
        $indonesianVictims = Cache::remember('cti_victims_indonesia', 21600, function () {
            try {
                $apiKey = env('RANSOMWARE_LIVE_API_KEY', 'bd446354-2877-42d9-91e1-23784c3e590e');
                $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'X-API-KEY' => $apiKey
                ])->timeout(10)->get('https://api-pro.ransomware.live/victims/?country=id');

                if ($response->successful()) {
                    return $response->json()['victims'] ?? [];
                }
                return [];
            } catch (\Throwable $e) {
                return [];
            }
        });

        return view('cti.external.index', compact('news', 'victims', 'topGroups', 'indonesianVictims'));
    }
    public function showGroup($name)
    {
        $group = Cache::remember('cti_group_' . $name, 43200, function () use ($name) {
            try {
                $apiKey = env('RANSOMWARE_LIVE_API_KEY', 'bd446354-2877-42d9-91e1-23784c3e590e');
                $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'X-API-KEY' => $apiKey
                ])->timeout(10)->get("https://api-pro.ransomware.live/groups/{$name}");

                if ($response->successful()) {
                    return $response->json();
                }
                return null;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Ransomware Group Detail API Error: " . $e->getMessage());
                return null;
            }
        });

        // Fetch IOCs (Separate Cache)
        $iocs = Cache::remember('cti_group_iocs_' . $name, 43200, function () use ($name) {
            try {
                $apiKey = env('RANSOMWARE_LIVE_API_KEY', 'bd446354-2877-42d9-91e1-23784c3e590e');
                $headers = [
                    'accept' => 'application/json',
                    'X-API-KEY' => $apiKey
                ];
                
                // Try original name
                $response = Http::withHeaders($headers)->timeout(10)->get("https://api-pro.ransomware.live/iocs/{$name}");
                if ($response->successful()) return $response->json();

                // Try ucfirst (e.g. qilin -> Qilin)
                $response = Http::withHeaders($headers)->timeout(10)->get("https://api-pro.ransomware.live/iocs/" . ucfirst($name));
                if ($response->successful()) return $response->json();

                // Try lowercase (e.g. Qilin -> qilin)
                $response = Http::withHeaders($headers)->timeout(10)->get("https://api-pro.ransomware.live/iocs/" . strtolower($name));
                if ($response->successful()) return $response->json();

                return null;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Ransomware IOC API Error: " . $e->getMessage());
                return null;
            }
        });

        // Fetch Negotiations List (Separate Cache)
        $negotiations = Cache::remember('cti_group_negotiations_' . $name, 43200, function () use ($name) {
            try {
                $apiKey = env('RANSOMWARE_LIVE_API_KEY', 'bd446354-2877-42d9-91e1-23784c3e590e');
                $headers = [
                    'accept' => 'application/json',
                    'X-API-KEY' => $apiKey
                ];
                
                // Try variations as some groups might have case mismatch
                $variations = [$name, ucfirst($name), strtolower($name), strtoupper($name)];
                
                foreach ($variations as $variant) {
                    $response = Http::withHeaders($headers)->timeout(10)->get("https://api-pro.ransomware.live/negotiations/{$variant}");
                    if ($response->successful()) return $response->json();
                }

                return null;
            } catch (\Throwable $e) {
                // Log but don't break page
                return null;
            }
        });

        if (!$group) {
            return redirect()->route('cti.external.index')->with('error', 'Group details not found.');
        }

        return view('cti.external.group_detail', compact('group', 'iocs', 'negotiations'));
    }

    public function showNegotiation($name, $chatId)
    {
        $chat = Cache::remember('cti_negotiation_' . $name . '_' . $chatId, 43200, function () use ($name, $chatId) {
            try {
                $apiKey = env('RANSOMWARE_LIVE_API_KEY', 'bd446354-2877-42d9-91e1-23784c3e590e');
                $headers = [
                    'accept' => 'application/json',
                    'X-API-KEY' => $apiKey
                ];

                 // Try variations 
                 $variations = [$name, ucfirst($name), strtolower($name), strtoupper($name)];
                
                 foreach ($variations as $variant) {
                     $url = "https://api-pro.ransomware.live/negotiations/{$variant}/{$chatId}";
                     $response = Http::withHeaders($headers)->timeout(10)->get($url);
                     if ($response->successful()) return $response->json();
                 }
                return null;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Negotiation Detail API Error: " . $e->getMessage());
                return null;
            }
        });

        if (!$chat) {
            return redirect()->back()->with('error', 'Chat transcript not found.');
        }

        return view('cti.external.negotiation_detail', compact('chat', 'name'));
    }
}
