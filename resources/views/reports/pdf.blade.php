<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $report['summary_json']['meta']['title'] ?? 'Security Incident Report' }}</title>

<style>
  /* =========================
     PAGE LAYOUT (anti nabrak)
     ========================= */
  @page{
    /* ruang aman untuk header & footer */
    margin: 90px 1cm 70px 1cm; /* TOP RIGHT BOTTOM LEFT */
    size: A4;
  }

  body{
    font-family: Helvetica, Arial, sans-serif;
    color:#0f172a;
    background:#ffffff;
    font-size: 9.2pt;
    margin:0; padding:0;
    line-height:1.55;
  }

  /* ===== UTILS ===== */
  .text-center{ text-align:center; }
  .text-right{ text-align:right; }
  .text-muted{ color:#64748b; }
  .text-blue{ color:#2563eb; }
  .text-red{ color:#dc2626; }
  .font-bold{ font-weight:700; }
  .font-mono{ font-family: "Courier New", Courier, monospace; }
  .uppercase{ text-transform:uppercase; letter-spacing:.04em; }

  .avoid-break{ page-break-inside: avoid; }

  /* page break yang “bersih” */
  .page-break{
    page-break-after: always;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
  }

  .divider{ height:1px; background:#e2e8f0; margin:12px 0; }

  /* =========================
     HEADER / FOOTER (fixed)
     ========================= */
  header{
    position: fixed;
    left: 0; right: 0;
    top: -70px;            /* harus <= margin-top (90px) */
    height: 45px;
    border-bottom: 2px solid #2563eb;
    text-align: right;
    font-size: 7pt;
    color: #94a3b8;
    padding-top: 10px;
  }

  footer{
    position: fixed;
    left: 0; right: 0;
    bottom: -55px;         /* harus <= margin-bottom (70px) */
    height: 40px;
    border-top: 1px solid #e2e8f0;
    text-align: center;
    font-size: 7.5pt;
    color: #94a3b8;
    padding-top: 10px;
  }

  /* ===== WRAPPER ===== */
  .container{ padding-top: 0 !important; }

  /* ===== CARDS ===== */
  .card{
    background:#ffffff;
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:16px 18px;
    margin-bottom:14px;
  }

  /* supaya elemen pertama di halaman tidak terlalu mepet */
  .card:first-child{ margin-top: 0; }

  .card-header{
    font-size:10.5pt;
    font-weight:800;
    color:#0f172a;
    padding-bottom:10px;
    margin-bottom:12px;
    border-bottom:1px solid #f1f5f9;
  }
  .card-subtitle{
    font-size:8pt;
    color:#94a3b8;
    margin-top:4px;
  }

  /* ===== BADGES ===== */
  .badge{
    display:inline-block;
    padding:3px 9px;
    border-radius:9999px;
    font-size:7pt;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.04em;
    vertical-align:middle;
    white-space:nowrap;
  }
  .badge-red{ background:#fee2e2; color:#991b1b; }
  .badge-green{ background:#dcfce7; color:#166534; }
  .badge-blue{ background:#dbeafe; color:#1e40af; }
  .badge-gray{ background:#f1f5f9; color:#475569; }
  .badge-orange{ background:#ffedd5; color:#9a3412; }

  /* ===== GRID (TABLE-BASED SAFE FOR PDF) ===== */
  table.grid{ width:100%; border-collapse:collapse; }
  table.grid td{ vertical-align:top; padding:0; }
  .col-gap{ width:14px; }
  .col-50{ width:50%; }
  .col-30{ width:30%; }
  .col-70{ width:70%; }

  /* ===== TABLES ===== */
  table.modern{
    width:100%;
    border-collapse:collapse;
    border:1px solid #e2e8f0;
    border-radius:10px;
  }
  table.modern thead th{
    text-align:left;
    padding:9px 10px;
    color:#64748b;
    font-size:8pt;
    font-weight:800;
    text-transform:uppercase;
    border-bottom:1px solid #e2e8f0;
    background:#f8fafc;
  }
  table.modern tbody td{
    padding:9px 10px;
    border-bottom:1px solid #f1f5f9;
    color:#0f172a;
  }
  table.modern tbody tr:nth-child(even) td{ background:#fcfdff; }
  table.modern tbody tr:last-child td{ border-bottom:none; }

  /* ===== METRICS ===== */
  .metric{
    text-align:center;
    padding:12px 10px;
    background:#f8fafc;
    border:1px solid #eef2f7;
    border-radius:10px;
  }
  .metric-value{
    font-size:16pt;
    font-weight:900;
    color:#0f172a;
    line-height:1.1;
  }
  .metric-label{
    font-size:7.5pt;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-top:4px;
  }

  /* ===== COVER ===== */
  .cover{
    padding:2.2cm 1.4cm 1.6cm 1.4cm;
  }
  .logo-img{ height:70px; margin-bottom:18px; }
  .report-title{
    font-size:24pt;
    font-weight:900;
    color:#0f172a;
    letter-spacing:-.6px;
    margin-bottom:6px;
  }
  .report-subtitle{
    font-size:11.5pt;
    color:#64748b;
    margin-bottom:18px;
  }
  .cover-box{
    margin:18px auto 0 auto;
    width:88%;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:18px 18px;
  }
  .cover-label{
    font-weight:800;
    color:#94a3b8;
    font-size:8pt;
    text-transform:uppercase;
    letter-spacing:.05em;
    display:block;
    margin-bottom:4px;
  }
  .cover-value{
    font-size:10.5pt;
    color:#0f172a;
    font-weight:800;
  }
  .cover-footer{
    margin-top:28px;
    color:#94a3b8;
    font-size:9pt;
  }

  /* ===== SECTION HEAD (Target) ===== */
  .section-head{
    width:100%;
    border-collapse:collapse;
    margin-bottom:12px;
  }
  .section-head td{ vertical-align:middle; }
  .target-title{
    font-size:14pt;
    font-weight:900;
    color:#0f172a;
  }

  /* ===== MINI PROGRESS ===== */
  .bar{
    height:7px;
    width:100%;
    background:#e2e8f0;
    border-radius:9999px;
    overflow:hidden;
  }
  .bar > div{
    height:7px;
    background:#ef4444;
    border-radius:9999px;
  }
</style>

</head>

<body>
<header>
    CONFIDENTIAL // SOC INCIDENT REPORT // {{ $report['period'] }}-{{ $report['id'] }}
</header>
<footer>
    Page <span class="page-number"></span> | Generated by Security Operations AI System
</footer>

<div class="container">

    <!-- COVER PAGE -->
    <div class="cover avoid-break">
        <div class="text-center">
            <img src="{{ public_path('images/mega-logo.png') }}" class="logo-img" alt="Logo">
            <div class="report-title">
                {{ $report['summary_json']['meta']['title'] ?? 'Security Incident Report' }}
            </div>
            <div class="report-subtitle">
                Automated Forensic Investigation &amp; Threat Analysis
            </div>
        </div>

        <div class="cover-box">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="padding:8px 6px;">
                        <span class="cover-label">Case ID</span>
                        <div class="cover-value font-mono">#{{ $report['period'] }}-{{ $report['id'] }}</div>
                    </td>
                    <td style="padding:8px 6px;">
                        <span class="cover-label">Date Generated</span>
                        <div class="cover-value">{{ \Carbon\Carbon::parse($report['created_at'])->format('M d, Y') }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 6px;">
                        <span class="cover-label">Classification</span>
                        <span class="badge badge-red" style="font-size:8.5pt;">
                            {{ $report['summary_json']['meta']['tlp'] ?? 'AMBER' }}
                        </span>
                    </td>
                    <td style="padding:8px 6px;">
                        <span class="cover-label">Status</span>
                        <span class="badge badge-blue" style="font-size:8.5pt;">
                            {{ $report['summary_json']['meta']['status'] ?? 'Closed' }}
                        </span>
                    </td>
                </tr>
            </table>
            <div style="margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 10px;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                         <td style="padding:8px 6px;">
                            <span class="cover-label">Priority</span>
                            <span class="badge {{ match($report['priority'] ?? '') { 'Critical' => 'badge-red', 'High' => 'badge-orange', 'Medium' => 'badge-blue', default => 'badge-gray' } }}" style="font-size:8.5pt;">
                                {{ $report['priority'] ?? 'N/A' }}
                            </span>
                        </td>
                        <td style="padding:8px 6px;">
                            <span class="cover-label">Severity</span>
                            <span class="badge {{ match($report['severity'] ?? '') { 'Critical' => 'badge-red', 'High' => 'badge-orange', 'Medium' => 'badge-blue', default => 'badge-gray' } }}" style="font-size:8.5pt;">
                                {{ $report['severity'] ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="text-center cover-footer">
            <div class="uppercase font-bold" style="color:#94a3b8;">PT ASURANSI UMUM MEGA</div>
            <div class="text-muted">Security Operations Center</div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- EXECUTIVE SUMMARY -->
    <div class="card">
        <div class="card-header">1. Executive Summary</div>

        <div style="font-size:10pt; color:#334155;">
            {{ $report['summary_json']['executive']['summary'] ?? 'N/A' }}
        </div>

        <div class="divider"></div>

        @if(($report['type'] ?? '') === 'General' && ($report['hostname'] || $report['internal_ip']))
            <div style="margin-bottom: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px;">
                <div class="uppercase font-bold" style="font-size:8pt; color:#64748b; margin-bottom:6px;">Affected Asset Details</div>
                <table style="width:100%; font-size: 8.5pt;">
                    <tr>
                        <td style="width: 25%; color: #64748b;">Hostname:</td>
                        <td style="width: 25%; font-weight: bold;">{{ $report['hostname'] ?? '-' }}</td>
                        <td style="width: 25%; color: #64748b;">IP Address:</td>
                        <td style="width: 25%; font-weight: bold; font-family: monospace;">{{ $report['internal_ip'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b;">OS:</td>
                        <td style="font-weight: bold;">{{ $report['os'] ?? '-' }}</td>
                        <td style="color: #64748b;">Zone:</td>
                        <td style="font-weight: bold;">{{ $report['network_zone'] ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="divider"></div>
        @endif

        @if(!empty($report['summary_json']['executive']['impact_analysis']))
            <div class="font-bold" style="margin-bottom:6px; color:#b91c1c;">Business Impact Analysis</div>
            <div class="text-muted" style="background:#fef2f2; border-left:3px solid #ef4444; padding:8px 12px; border-radius:4px; margin-bottom:12px;">
                {{ $report['summary_json']['executive']['impact_analysis'] }}
            </div>
            <div class="divider"></div>
        @endif

        <table class="grid">
            <tr>
                <td class="col-50">
                    <div class="metric">
                        <div class="metric-value text-red">
                            {{ $report['summary_json']['executive']['risk_score'] ?? 'High' }}
                        </div>
                        <div class="metric-label">Risk Level</div>
                    </div>
                </td>
                <td class="col-gap"></td>
                <td class="col-50">
                    <div class="metric">
                        <div class="metric-value text-blue">High</div>
                        <div class="metric-label">Confidence</div>
                    </div>
                </td>
            </tr>
        </table>

        @if(isset($report['summary_json']['technical']['root_cause']) && $report['summary_json']['technical']['root_cause'] !== 'Unknown')
        <div class="divider"></div>
        <div class="font-bold" style="margin-bottom:6px;">Strategic Analysis & Root Cause</div>
        <div class="text-muted" style="background:#fefce8; border-left:3px solid #eab308; padding:8px 12px; border-radius:4px;">
            {{ $report['summary_json']['technical']['root_cause'] }}
        </div>
        @endif

        <div class="divider"></div>

        <div class="font-bold" style="margin-bottom:6px;">Recommendations</div>
        <div class="text-muted">
            {!! nl2br(e($report['summary_json']['executive']['recommendations'] ?? $report['summary_json']['recommendations'] ?? 'N/A')) !!}
        </div>
    </div>

    <!-- TECHNICAL ANALYSIS -->
    <div class="card">
        <div class="card-header">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="padding:0;">2. Technical Analysis &amp; Timeline</td>
                    <td class="text-right" style="padding:0;">
                        @if(!empty($report['summary_json']['technical']['kill_chain_phase']))
                            <span class="badge badge-blue">Phase: {{ $report['summary_json']['technical']['kill_chain_phase'] }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="text-muted" style="margin-bottom:12px;">
            {!! nl2br(e($report['summary_json']['technical']['analysis'] ?? 'Detailed analysis unavailable.')) !!}
        </div>

        @if(!empty($report['summary_json']['technical']['mitre_techniques']))
            <div style="margin-bottom:15px;">
                <div class="uppercase font-bold" style="font-size:7pt; color:#94a3b8; margin-bottom:6px;">MITRE ATT&CK Matrix</div>
                @foreach($report['summary_json']['technical']['mitre_techniques'] as $tech)
                    <div style="font-size:7.5pt; margin-bottom:2px; font-family:monospace; background:#f3f4f6; padding:2px 4px; border-radius:3px; display:inline-block; border:1px solid #e5e7eb;">
                        {{ $tech }}
                    </div>
                @endforeach
            </div>
        @elseif(!empty($report['summary_json']['technical']['mitre_tactics']))
            <div style="margin-bottom:15px;">
                <div class="uppercase font-bold" style="font-size:7pt; color:#94a3b8; margin-bottom:6px;">MITRE ATT&CK Tactics</div>
                @foreach($report['summary_json']['technical']['mitre_tactics'] as $tactic)
                    <span class="badge badge-orange" style="font-size:7.5pt;">{{ $tactic }}</span>
                @endforeach
            </div>
        @endif

        @if(isset($report['summary_json']['forensics']['timeline']) && count($report['summary_json']['forensics']['timeline']) > 0)
            <div class="uppercase font-bold" style="font-size:8pt; color:#94a3b8; margin-bottom:10px;">Incident Timeline</div>
            <table class="modern">
                <thead>
                    <tr>
                        <th style="width:110px;">Time</th>
                        <th style="width:120px;">Event Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['summary_json']['forensics']['timeline'] as $event)
                        <tr>
                            <td class="font-mono text-muted" style="font-size:8.5pt;">{{ $event['time'] }}</td>
                            <td class="font-bold">{{ $event['type'] }}</td>
                            <td class="text-muted">{{ $event['desc'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if(!empty($report['summary_json']['forensics']['artifacts']))
        <div class="card avoid-break">
             <div class="card-header">3. Evidence & Attached Artifacts</div>
             
             <div style="padding: 10px;">
                @foreach($report['summary_json']['forensics']['artifacts'] as $artifact)
                    @php
                        $ext = strtolower(pathinfo($artifact['name'], PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        
                        // Fix: DB might store 'public/reports/...' but storage_path needs 'reports/...'
                        // We strip 'public/' prefix if present to ensure correct path resolution.
                        $cleanPath = str_replace('public/', '', $artifact['path']);
                        
                        // Try native storage path first (best for DomPDF)
                        // storage_path('app/public') -> D:\...\storage\app\public
                        $path = storage_path('app/public/' . $cleanPath);
                        
                        // Fallback to public symlink path if storage path fails
                        if (!file_exists($path)) {
                            $path = public_path('storage/' . $cleanPath);
                        }
                    @endphp

                    <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; page-break-inside: avoid;">
                        <div style="font-size: 9pt; font-weight: bold; margin-bottom: 5px; color: #334155;">
                            {{ $artifact['name'] }} <span style="font-weight: normal; color: #94a3b8; font-size: 8pt;">({{ number_format($artifact['size'] / 1024, 1) }} KB)</span>
                        </div>

                        @if($isImage && file_exists($path))
                            <div style="text-align: center; margin-top: 10px;">
                                <img src="{{ $path }}" style="max-width: 100%; max-height: 500px; border-radius: 4px; border: 1px solid #e2e8f0;">
                            </div>
                        @else
                            <div style="background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 8pt; color: #64748b;">
                                <i>
                                    @if(!$isImage)
                                        File type ({{ strtoupper($ext) }}) is not supported for PDF preview.
                                    @else
                                        Image could not be loaded (File not found).
                                    @endif
                                </i>
                            </div>
                        @endif
                    </div>
                 @endforeach
             </div>
        </div>
    @endif

    <div class="page-break"></div>

    <!-- THREAT INTEL -->
    @foreach($intelSources as $index => $intel)

        <div class="avoid-break">

            <!-- TARGET HEADER (PDF-safe) -->
            <table class="section-head">
                <tr>
                    <td>
                        <div class="target-title">
                            Target: <span class="text-blue">{{ $intel->ip_address }}</span>
                        </div>
                        <div class="text-muted" style="font-size:8pt; margin-top:2px;">
                            {{ $intel->ip_info['org'] ?? 'Unknown Organization' }} • 
                            @if(isset($intel->abuseipdb['domain'])) {{ $intel->abuseipdb['domain'] }} @else DNS Resolution Pending @endif
                        </div>
                    </td>
                    <td class="text-right" style="width:200px;">
                        <span class="badge {{ $intel->risk_score > 75 ? 'badge-red' : ($intel->risk_score > 40 ? 'badge-orange' : 'badge-green') }}" style="font-size:8.5pt; padding:5px 12px;">
                            Risk Score: {{ $intel->risk_score }}/100
                        </span>
                    </td>
                </tr>
            </table>

            <!-- NETWORK CARDS -->
            <table class="grid">
                <tr>
                    <td class="col-50">
                        <div class="card" style="margin-bottom:14px; min-height:130px;">
                            <div class="uppercase font-bold" style="font-size:8pt; color:#94a3b8; margin-bottom:10px;">
                                Network Identity
                            </div>
                            <div class="font-bold" style="font-size:10pt; color:#1e293b;">
                                {{ $intel->ip_info['org'] ?? 'Unknown Org' }}
                            </div>
                            <div class="font-mono text-muted" style="margin-top:4px; font-size:8.5pt;">
                                ASN: {{ $intel->ip_info['asn'] ?? 'N/A' }} | ISP: {{ $intel->abuseipdb['isp'] ?? 'N/A' }}
                            </div>

                            <div style="margin-top:10px;">
                                <span class="badge badge-gray" style="font-size:7.5pt;">{{ $intel->ip_info['country'] ?? 'UNK' }}</span>
                                <span class="badge badge-gray" style="font-size:7.5pt;">{{ $intel->ip_info['city'] ?? '-' }}</span>
                                @if(isset($intel->abuseipdb['usageType']))
                                    <span class="badge badge-blue" style="font-size:7.5pt;">{{ $intel->abuseipdb['usageType'] }}</span>
                                @endif
                                @if(isset($intel->abuseipdb['isTor']) && $intel->abuseipdb['isTor'])
                                    <span class="badge badge-red" style="font-size:7.5pt;">TOR EXIT</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="col-gap"></td>
                    <td class="col-50">
                        <div class="card" style="margin-bottom:14px; min-height:130px;">
                            <div class="uppercase font-bold" style="font-size:8pt; color:#94a3b8; margin-bottom:10px;">
                                Source Intelligence
                            </div>
                            @if(!empty($intel->greynoise) && isset($intel->greynoise['classification']))
                                <div style="margin-bottom:8px;">
                                    <span class="text-muted" style="font-size:9pt;">GreyNoise:</span>
                                    <span class="badge {{ $intel->greynoise['classification'] === 'malicious' ? 'badge-red' : ($intel->greynoise['classification'] === 'benign' ? 'badge-green' : 'badge-gray') }}">
                                        {{ $intel->greynoise['classification'] }}
                                    </span>
                                    @if(isset($intel->greynoise['actor']))
                                        <div class="font-bold" style="margin-top:4px; font-size:9pt;">Actor: {{ $intel->greynoise['actor'] }}</div>
                                    @endif
                                </div>
                            @else
                                <div class="text-muted" style="font-size:9pt; margin-bottom:12px;">GreyNoise: No noise detected</div>
                            @endif

                            @if(isset($intel->abuseipdb['abuseConfidenceScore']))
                                <div style="margin-top:10px;">
                                    <span class="text-muted" style="font-size:9pt;">Abuse Confidence:</span>
                                    <span class="font-bold {{ $intel->abuseipdb['abuseConfidenceScore'] > 50 ? 'text-red' : 'text-blue' }}">
                                        {{ $intel->abuseipdb['abuseConfidenceScore'] }}%
                                    </span>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <!-- VIRUSTOTAL -->
            @php
                $vt = $intel->virustotal['last_analysis_results'] ?? [];
                $malicious = collect($vt)->whereIn('category', ['malicious','suspicious']);
            @endphp

            <div class="card" style="margin-top:0;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:0;">
                            <div class="card-header" style="border-bottom:none; margin-bottom:6px; padding-bottom:0;">
                                VirusTotal Reputation Scan
                                <div class="card-subtitle">Global Security Vendor Assessment</div>
                            </div>
                        </td>
                        <td class="text-right" style="padding:0; width:220px;">
                            <span class="badge {{ $malicious->count() > 0 ? 'badge-red' : 'badge-green' }}" style="font-size:8.5pt; padding:4px 12px;">
                                {{ $malicious->count() }} / {{ count($vt) }} Engines Check
                            </span>
                        </td>
                    </tr>
                </table>

                <div class="divider"></div>

                @if($malicious->count() > 0)
                    <table class="grid">
                        <tr>
                            <td class="col-30">
                                <div class="metric" style="background:#fee2e2; border-color:#fecaca;">
                                    <div class="metric-value text-red">{{ $malicious->count() }}</div>
                                    <div class="metric-label" style="color:#991b1b;">Malicious Flags</div>
                                </div>
                            </td>
                            <td class="col-gap"></td>
                            <td class="col-70">
                                <div class="font-bold text-red" style="margin-bottom:8px; font-size:8.5pt;">Critical Detections:</div>
                                <div>
                                    @php $count = 0; @endphp
                                    @foreach($malicious->take(12) as $vendor => $res)
                                        <span class="badge badge-red" style="float:left; margin-right:4px; margin-bottom:4px; font-size:7pt;">
                                            {{ $vendor }}: {{ $res['result'] }}
                                        </span>
                                    @endforeach
                                    <div style="clear:both;"></div>
                                    @if($malicious->count() > 12)
                                        <span class="text-muted" style="font-size:7.5pt;">+ {{ $malicious->count() - 12 }} more engines</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="text-center" style="padding:15px; background:#f0fdf4; border:1px dashed #bbf7d0; border-radius:8px;">
                        <div class="text-green font-bold">REPUTATION: CLEAN</div>
                        <div class="text-muted" style="font-size:8.5pt; margin-top:4px;">None of the {{ count($vt) }} security engines analyzed this target as malicious.</div>
                    </div>
                @endif
            </div>

            <!-- ABUSEIPDB DETAILS -->
            @if(isset($intel->abuseipdb['reports']) && count($intel->abuseipdb['reports']) > 0)
                <div class="card">
                    <div class="card-header">Community Intelligence (AbuseIPDB)</div>

                    <table class="grid">
                        <tr>
                            <td class="col-30">
                                <div class="metric">
                                    <div class="metric-value" style="color:#f59e0b;">{{ $intel->abuseipdb['totalReports'] ?? count($intel->abuseipdb['reports']) }}</div>
                                    <div class="metric-label">Public Reports</div>
                                </div>
                                <div style="height:14px;"></div>
                                @if(isset($intel->abuseipdb['lastReportedAt']))
                                    <div class="text-center">
                                        <div class="uppercase font-bold" style="font-size:6.5pt; color:#94a3b8;">Last Activity</div>
                                        <div class="font-mono" style="font-size:8pt;">{{ \Carbon\Carbon::parse($intel->abuseipdb['lastReportedAt'])->diffForHumans() }}</div>
                                    </div>
                                @endif
                            </td>

                            <td class="col-gap"></td>

                            <td class="col-70">
                                @php
                                    $catMap = [
                                        1 => 'DNS Compromise', 2 => 'DNS Poisoning', 3 => 'Fraud Orders',
                                        4 => 'DDoS Attack', 5 => 'FTP Brute-Force', 6 => 'Ping of Death',
                                        7 => 'Phishing', 8 => 'Fraud VoIP', 9 => 'Open Proxy',
                                        10 => 'Web Spam', 11 => 'Email Spam', 12 => 'Blog Spam',
                                        13 => 'VPN IP', 14 => 'Port Scan', 15 => 'Hacking',
                                        16 => 'SQL Injection', 17 => 'Spoofing', 18 => 'Brute-Force',
                                        19 => 'Bad Web Bot', 20 => 'Exploited Host', 21 => 'Web App Attack',
                                        22 => 'SSH', 23 => 'IoT Targeted'
                                    ];

                                    $categories = [];
                                    $comments = [];
                                    foreach($intel->abuseipdb['reports'] as $rep) {
                                        if(!empty($rep['comment'])) {
                                            $comments[] = $rep['comment'];
                                        }
                                        foreach($rep['categories'] as $cat) {
                                            $categories[$cat] = ($categories[$cat] ?? 0) + 1;
                                        }
                                    }
                                    arsort($categories);
                                    $topCats = array_slice($categories, 0, 4, true);
                                    $maxCat = max($categories ?: [1]);
                                @endphp

                                <div class="uppercase font-bold" style="font-size:7.5pt; color:#94a3b8; margin-bottom:8px;">Attack Classification Distribution</div>
                                @foreach($topCats as $catId => $count)
                                    <div style="margin-bottom:10px;">
                                        <table style="width:100%; font-size:8pt; margin-bottom:3px;">
                                            <tr>
                                                <td class="font-bold">{{ $catMap[$catId] ?? 'Category '.$catId }}</td>
                                                <td class="text-right text-muted">{{ $count }} reports</td>
                                            </tr>
                                        </table>
                                        <div class="bar">
                                            <div style="width: {{ min(($count / $maxCat) * 100, 100) }}%; background: {{ in_array($catId, [15, 18, 21]) ? '#dc2626' : '#f59e0b' }};"></div>
                                        </div>
                                    </div>
                                @endforeach

                                @if(!empty($comments))
                                    <div style="margin-top:15px; padding:10px; background:#f1f5f9; border-radius:6px; border-left:3px solid #64748b;">
                                        <div class="uppercase font-bold" style="font-size:7pt; color:#475569; margin-bottom:6px;">Latest Community Comments</div>
                                        @foreach(array_slice($comments, 0, 2) as $comment)
                                            <div style="font-size:8pt; color:#334155; margin-bottom:8px; line-height:1.4; font-style:italic;">
                                                "{{ \Illuminate\Support\Str::limit($comment, 150) }}"
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

            <!-- OTX PULSES (DEEP FORENSICS) -->
            @if(isset($intel->alienvault['pulses']) && count($intel->alienvault['pulses']) > 0)
                <div class="card" style="border-left:4px solid #2563eb;">
                    <div class="card-header" style="border-bottom:none; margin-bottom:0;">
                        AlienVault OTX Deep Forensics
                        <div class="card-subtitle">Global Threat Intelligence Pulses (DB Sourced)</div>
                    </div>
                    
                    @foreach(array_slice($intel->alienvault['pulses'], 0, 3) as $pulse)
                        <div style="margin-top:15px; padding:15px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; border-left:4px solid #1e40af;">
                            
                            <!-- PULSE INFO -->
                            <table style="width:100%; border-collapse:collapse; margin-bottom:12px;">
                                <tr>
                                    <td>
                                        <div class="font-bold" style="color:#1e40af; font-size:10.5pt; line-height:1.2;">
                                            {{ $pulse['name'] }}
                                        </div>
                                        <div class="text-muted" style="font-size:8pt; margin-top:4px;">
                                            @if(!empty($pulse['adversary'])) Adversary: <span class="font-bold">{{ $pulse['adversary'] }}</span> • @endif
                                            Created: {{ \Carbon\Carbon::parse($pulse['created'] ?? now())->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="text-right" style="width:120px;">
                                        <span class="badge badge-blue">ID: {{ substr($pulse['id'], 0, 8) }}</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- TAG CLOUD -->
                            @if(!empty($pulse['tags']))
                                <div style="margin-bottom:12px;">
                                    @foreach(array_slice($pulse['tags'], 0, 30) as $tag)
                                        <span class="badge badge-gray" style="font-size:6.5pt; margin-right:3px; margin-bottom:3px; background:#fff; border:1px solid #cbd5e1;">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="text-muted" style="margin-bottom:12px; font-size:9pt; line-height:1.4;">
                                {{ \Illuminate\Support\Str::limit($pulse['description'] ?? 'No description available in DB.', 300) }}
                            </div>

                            @if(!empty($pulse['malware_families']))
                                <div style="margin-bottom:12px;">
                                    <span class="uppercase font-bold" style="font-size:7pt; color:#94a3b8;">Malware Families:</span>
                                    @foreach($pulse['malware_families'] as $family)
                                        <span class="badge badge-red" style="font-size:7pt;">{{ $family }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- ASSOCIATED IOCS (DB SOURCE) -->
                            @if(!empty($pulse['indicators']))
                                <div style="background:#fff; border:1px dashed #cbd5e1; border-radius:6px; padding:10px;">
                                    <table style="width:100%; border-collapse:collapse;">
                                        <tr>
                                            <td colspan="2" class="uppercase font-bold" style="font-size:7.5pt; color:#64748b; padding-bottom:8px; border-bottom:1px solid #f1f5f9;">
                                                Associated Indicators (Latest 10 from DB)
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top:10px;">
                                                <table style="width:100%; font-size:8pt;">
                                                    @foreach(array_slice($pulse['indicators'], 0, 10) as $ioc)
                                                        <tr>
                                                            <td style="width:70px; color:#ef4444;" class="font-bold">[{{ $ioc['type'] }}]</td>
                                                            <td class="font-mono text-muted" style="word-break: break-all;">{{ $ioc['indicator'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach

</div>
</body>
</html>
