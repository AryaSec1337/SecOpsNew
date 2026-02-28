<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgentConfigController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'config' => [
                'vt_api_key' => env('VIRUSTOTAL_API_KEY'),
                // Future config items can go here (e.g., FIM paths, log paths)
            ]
        ]);
    }
}
