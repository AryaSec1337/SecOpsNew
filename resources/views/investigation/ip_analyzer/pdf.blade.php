<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Intelligence Report - {{ $data['ip'] ?? 'N/A' }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', Arial, sans-serif; margin: 0; color: #334155; font-size: 12px; line-height: 1.5; }
        
        /* Layout Helpers */
        .w-full { width: 100%; }
        .w-half { width: 50%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .align-top { vertical-align: top; }
        .mb-20 { margin-bottom: 20px; }
        .p-40 { padding: 40px; }
        
        /* Header */
        .page-header { background-color: #0f172a; color: white; padding: 30px 40px; }
        .report-title { font-size: 20px; font-weight: bold; margin: 0; letter-spacing: 1px; text-transform: uppercase; }
        .report-subtitle { font-size: 10px; color: #cbd5e1; margin-top: 5px; text-transform: uppercase; }
        .logo { height: 40px; }

        /* Tables for Layout */
        table.layout { width: 100%; border-collapse: collapse; }
        table.layout td { padding: 0; vertical-align: top; }
        
        /* Card Styles */
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 20px; overflow: hidden; }
        .card-header { background: #f8fafc; padding: 8px 15px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #475569; font-size: 10px; text-transform: uppercase; }
        .card-body { padding: 15px; }

        /* Typography */
        .label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 2px; display: block; }
        .value { font-size: 13px; font-weight: bold; color: #0f172a; font-family: 'Courier New', monospace; }
        .value-lg { font-size: 24px; font-weight: bold; }

        /* Risk Colors */
        .text-risk-high { color: #dc2626; }
        .text-risk-medium { color: #d97706; }
        .text-risk-low { color: #059669; }
        .bg-risk-high { background-color: #fef2f2; }
        .bg-risk-medium { background-color: #fffbeb; }
        .bg-risk-low { background-color: #ecfdf5; }
        .border-risk-high { border: 2px solid #fee2e2; }
        .border-risk-medium { border: 2px solid #fef3c7; }
        .border-risk-low { border: 2px solid #d1fae5; }

        /* Data Tables */
        table.data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.data-table th { text-align: left; padding: 8px; background: #f1f5f9; color: #475569; border-bottom: 1px solid #e2e8f0; text-transform: uppercase; font-size: 9px; }
        table.data-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        
        /* Footer */
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 15px 40px; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; background: white; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="page-header">
        <table class="layout">
            <tr>
                <td style="width: 50%;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/3f/Mega_Insurance_Logo.png" class="logo" alt="Mega Insurance">
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="report-title">Threat IP Analyzer Report</div>
                    <div class="report-subtitle">Ref: {{ strtoupper(substr(md5($data['ip'] ?? time()), 0, 10)) }} | {{ date('d M Y H:i') }} UTC</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="p-40">
        
        <!-- Summary Section -->
        <table class="layout mb-20">
            <tr>
                <!-- Target Assessment -->
                <td style="padding-right: 15px;">
                    <div class="card">
                        <div class="card-header">Target Assessment</div>
                        <div class="card-body">
                            <span class="label">IP Address</span>
                            <div class="value value-lg">{{ $data['ip'] ?? 'N/A' }}</div>
                            <br>
                            <table class="layout">
                                <tr>
                                    <td>
                                        <span class="label">Country</span>
                                        <div class="value">{{ $data['geo']['country'] ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="label">Network (ASN)</span>
                                        <div class="value" style="font-size: 11px;">{{ Str::limit($data['geo']['org'] ?? '-', 30) }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                
                <!-- Risk Score -->
                <td style="width: 140px;">
                    @php
                        $score = $data['risk_score'] ?? 0;
                        $riskClass = $score >= 70 ? 'risk-high' : ($score >= 30 ? 'risk-medium' : 'risk-low');
                        $riskLabel = $score >= 70 ? 'CRITICAL' : ($score >= 30 ? 'SUSPICIOUS' : 'SAFE');
                    @endphp
                    <div class="card bg-{{ $riskClass }} border-{{ $riskClass }}" style="text-align: center;">
                        <div class="card-body">
                             <span class="label" style="margin-bottom: 5px; display: block;">Overall Risk</span>
                             <div class="value-lg text-{{ $riskClass }}" style="font-size: 36px; margin: 5px 0;">{{ $score }}</div>
                             <div style="font-weight: bold; font-size: 11px; letter-spacing: 1px;" class="text-{{ $riskClass }}">{{ $riskLabel }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Intelligence Detail -->
        <h3 style="margin: 0 0 10px 0; font-size: 12px; text-transform: uppercase; color: #0f172a;">Intelligence Sources</h3>
        
        <table class="layout mb-20" style="table-layout: fixed;">
            <tr>
                <!-- VirusTotal -->
                <td style="padding-right: 10px;">
                     <div class="card">
                        <div class="card-header" style="border-top: 2px solid #ef4444;">VirusTotal</div>
                        <div class="card-body">
                            <table class="layout">
                                <tr>
                                    <td>
                                        <div class="value-lg text-risk-high">{{ $data['virustotal']['last_analysis_stats']['malicious'] ?? 0 }}</div>
                                        <span class="label">Detections</span>
                                    </td>
                                    <td class="text-right">
                                         <div class="value">{{ $data['virustotal']['reputation'] ?? 0 }}</div>
                                         <span class="label">Reputation</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>

                <!-- AbuseIPDB -->
                <td style="padding-right: 10px;">
                    <div class="card">
                        <div class="card-header" style="border-top: 2px solid #f59e0b;">AbuseIPDB</div>
                        <div class="card-body">
                            <table class="layout">
                                <tr>
                                    <td>
                                        <div class="value-lg text-risk-medium">{{ $data['abuseipdb']['abuseConfidenceScore'] ?? 0 }}%</div>
                                        <span class="label">Confidence</span>
                                    </td>
                                     <td class="text-right">
                                         <div class="value">{{ $data['abuseipdb']['totalReports'] ?? 0 }}</div>
                                         <span class="label">Reports</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>

                <!-- AlienVault -->
                <td>
                    <div class="card">
                        <div class="card-header" style="border-top: 2px solid #8b5cf6;">AlienVault OTX</div>
                        <div class="card-body">
                            <table class="layout">
                                <tr>
                                    <td>
                                        <div class="value-lg">{{ $data['alienvault']['pulse_count'] ?? 0 }}</div>
                                        <span class="label">Pulses</span>
                                    </td>
                                     <td class="text-right">
                                         <div class="value">{{ isset($data['alienvault']['pulses']) ? count($data['alienvault']['pulses']) : 0 }}</div>
                                         <span class="label">Associated</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Detailed Reports Table -->
        @if(isset($data['abuseipdb']['reports']) && count($data['abuseipdb']['reports']) > 0)
        <div class="card">
            <div class="card-header">Recent Abuse Reports</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="80">Date</th>
                        <th width="120">Categories</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($data['abuseipdb']['reports'], 0, 10) as $report)
                    <tr>
                        <td>{{ date('d M Y', strtotime($report['reportedAt'])) }}</td>
                        <td>
                            @php
                                // Inline Map
                                $catMap = [
                                    3 => 'Fraud', 4 => 'DDoS', 9 => 'Open Proxy', 10 => 'Web Spam',
                                    11 => 'Email Spam', 14 => 'Port Scan', 15 => 'Hacking', 18 => 'Brute-Force',
                                    19 => 'Web Bot', 20 => 'Exploited', 21 => 'Web Attack', 
                                    22 => 'SSH', 23 => 'IoT'
                                ];
                            @endphp
                            @foreach($report['categories'] as $cat)
                                <span style="background: #f1f5f9; padding: 1px 3px; font-size: 8px; border-radius: 2px; color: #475569; margin-right: 2px;">
                                    {{ $catMap[$cat] ?? $cat }}
                                </span>
                            @endforeach
                        </td>
                        <td style="font-style: italic; color: #64748b;">
                            {{ $report['comment'] ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- OTX Pulses -->
        @if(isset($data['alienvault']['pulses']) && count($data['alienvault']['pulses']) > 0)
        <div class="card">
            <div class="card-header">AlienVault OTX Pulses & Indicators</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="200">Pulse Details</th>
                        <th>Top Indicators (IOCs)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($data['alienvault']['pulses'], 0, 5) as $pulse)
                    <tr>
                        <td>
                            <strong style="color: #6d28d9;">{{ $pulse['name'] }}</strong><br>
                            <span style="font-size: 9px; color: #94a3b8;">{{ $pulse['id'] }}</span>
                            <div style="margin-top: 5px;">
                                 @foreach(array_slice($pulse['tags'], 0, 5) as $tag)
                                    <span style="font-size: 8px; color: #64748b; background: #f8fafc; padding: 1px 3px; border: 1px solid #e2e8f0; border-radius: 2px; display: inline-block; margin-bottom: 2px;">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                             @if(isset($pulse['indicators']) && count($pulse['indicators']) > 0)
                                <table style="width: 100%; border: none;">
                                    @foreach(array_slice($pulse['indicators'], 0, 5) as $ioc)
                                    <tr>
                                        <td style="padding: 2px 0; border: none; font-size: 9px; font-family: monospace;">
                                            <span style="color: #ef4444; font-weight: bold;">{{ $ioc['type'] ?? 'IOC' }}</span>: {{ $ioc['indicator'] ?? '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            @else
                                <span style="font-style: italic; color: #94a3b8;">No specific indicators listed.</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    <div class="page-footer">
        <table class="layout">
            <tr>
                <td>Generated by SecOps Security Operations Center</td>
                <td class="text-right">CONFIDENTIAL - INTERNAL USE ONLY</td>
            </tr>
        </table>
    </div>

</body>
</html>
