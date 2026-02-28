<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MitreController extends Controller
{
    public function index()
    {
        // Cache MITRE data for 24 hours (86400 seconds) as it rarely changes
        $matrix = Cache::remember('mitre_attack_matrix', 86400, function () {
            try {
                // Official MITRE CTI Repo (Enterprise)
                $url = "https://raw.githubusercontent.com/mitre/cti/master/enterprise-attack/enterprise-attack.json";
                $response = Http::timeout(30)->get($url);
                
                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json();
                $objects = collect($data['objects'] ?? []);

                // 1. Get Tactics (Columns)
                $tactics = $objects->where('type', 'x-mitre-tactic')->map(function ($tactic) {
                    return [
                        'name' => $tactic['name'],
                        'short_name' => $tactic['x_mitre_shortname'],
                        'description' => $tactic['description'] ?? '',
                        'external_id' => $tactic['external_references'][0]['external_id'] ?? '',
                        'order' => $this->getTacticOrder($tactic['x_mitre_shortname']) // Custom sorting
                    ];
                })->sortBy('order')->values();

                // 2. Get Techniques (Rows)
                $techniques = $objects->where('type', 'attack-pattern')->where('revoked', false)->map(function ($tech) {
                    $phases = collect($tech['kill_chain_phases'] ?? [])->where('kill_chain_name', 'mitre-attack');
                    return [
                        'id' => $tech['external_references'][0]['external_id'] ?? '',
                        'name' => $tech['name'],
                        'description' => $tech['description'] ?? '',
                        'tactics' => $phases->pluck('phase_name')->toArray(),
                        'platforms' => $tech['x_mitre_platforms'] ?? [],
                        'url' => $tech['external_references'][0]['url'] ?? '#'
                    ];
                });

                // 3. Map Techniques to Tactics
                $matrix = $tactics->map(function ($tactic) use ($techniques) {
                    $tacticTechs = $techniques->filter(function ($tech) use ($tactic) {
                        return in_array($tactic['short_name'], $tech['tactics']);
                    })->sortBy('name')->values();

                    return [
                        'tactic' => $tactic,
                        'techniques' => $tacticTechs
                    ];
                });

                return $matrix;

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("MITRE Fetch Error: " . $e->getMessage());
                return [];
            }
        });

        return view('cti.mitre.index', compact('matrix'));
    }

    private function getTacticOrder($shortName)
    {
        $order = [
            'reconnaissance' => 1,
            'resource-development' => 2,
            'initial-access' => 3,
            'execution' => 4,
            'persistence' => 5,
            'privilege-escalation' => 6,
            'defense-evasion' => 7,
            'credential-access' => 8,
            'discovery' => 9,
            'lateral-movement' => 10,
            'collection' => 11,
            'command-and-control' => 12,
            'exfiltration' => 13,
            'impact' => 14
        ];

        return $order[$shortName] ?? 99;
    }
}
