<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <style type="text/css">
        /* Reset */
        body, table, td, p, a, li, blockquote { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        
        /* Dark mode support for non-Outlook clients */
        @media (prefers-color-scheme: dark) {
            .email-bg { background-color: #0f172a !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#1e293b; font-family:Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased;">
    
    <!--[if mso]>
    <style type="text/css">
        body, table, td { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->

    {{-- Full-width background wrapper --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#1e293b;" bgcolor="#1e293b" class="email-bg">
        <tr>
            <td align="center" style="padding:30px 10px;" bgcolor="#1e293b">

                {{-- Main container 600px --}}
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px; width:100%;">

                    {{-- ============================================ --}}
                    {{-- TOP ALERT BAR --}}
                    {{-- ============================================ --}}
                    <tr>
                        <td bgcolor="{{ $scan->verdict === 'MALICIOUS' ? '#991b1b' : '#92400e' }}" style="padding:16px 24px; background-color:{{ $scan->verdict === 'MALICIOUS' ? '#991b1b' : '#92400e' }};">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-size:13px; color:#ffffff; font-weight:bold; letter-spacing:1px; text-transform:uppercase;">
                                        &#9888; SECOPS THREAT ALERT
                                    </td>
                                    <td align="right" style="font-size:12px; color:#fbbf24; font-family:Arial;">
                                        {{ now()->format('d M Y, H:i') }} WIB
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ============================================ --}}
                    {{-- MAIN CONTENT --}}
                    {{-- ============================================ --}}
                    <tr>
                        <td bgcolor="#0f172a" style="background-color:#0f172a; padding:0;">

                            {{-- Verdict Section --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td bgcolor="#0f172a" style="background-color:#0f172a; padding:28px 24px 20px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size:28px; font-weight:bold; color:#f8fafc; line-height:1.2;">
                                                    Threat Detected
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:12px;">
                                                    <!--[if mso]>
                                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" style="height:28px;v-text-anchor:middle;width:120px;" arcsize="15%" stroke="f" fillcolor="{{ $scan->verdict === 'MALICIOUS' ? '#dc2626' : '#d97706' }}">
                                                    <w:anchorlock/>
                                                    <center style="color:#ffffff;font-family:Arial;font-size:12px;font-weight:bold;letter-spacing:1px;">{{ $scan->verdict }}</center>
                                                    </v:roundrect>
                                                    <![endif]-->
                                                    <!--[if !mso]><!-->
                                                    <span style="display:inline-block; padding:6px 16px; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#ffffff; background-color:{{ $scan->verdict === 'MALICIOUS' ? '#dc2626' : '#d97706' }}; border-radius:4px; mso-hide:all;">{{ $scan->verdict }}</span>
                                                    <!--<![endif]-->
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Detected By --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td bgcolor="#0f172a" style="background-color:#0f172a; padding:0 24px 20px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="font-size:10px; color:#64748b; text-transform:uppercase; letter-spacing:2px; padding-bottom:6px; font-weight:bold;">
                                                    DETECTED BY
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:15px; font-weight:bold; color:#f97316;">
                                                    {{ $detectedBy }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Separator --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td bgcolor="#0f172a" style="background-color:#0f172a; padding:0 24px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="border-top:1px solid #334155; font-size:1px; height:1px; line-height:1px;">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- ============================================ --}}
                            {{-- FILE INFORMATION --}}
                            {{-- ============================================ --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td bgcolor="#0f172a" style="background-color:#0f172a; padding:20px 24px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #334155;" bgcolor="#1e293b">
                                            <tr>
                                                <td bgcolor="#1e293b" style="background-color:#1e293b; padding:16px 18px 8px;">
                                                    <p style="margin:0 0 12px 0; font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:2px; font-weight:bold;">
                                                        &#128196; FILE INFORMATION
                                                    </p>

                                                    {{-- File info rows --}}
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                                        <tr>
                                                            <td width="110" valign="top" style="padding:6px 0; font-size:13px; color:#94a3b8;">Filename</td>
                                                            <td valign="top" style="padding:6px 0; font-size:13px; color:#f1f5f9; font-weight:bold;">{{ $scan->original_filename }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" style="border-bottom:1px solid #334155; font-size:1px; height:1px;">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="110" valign="top" style="padding:6px 0; font-size:13px; color:#94a3b8;">SHA-256</td>
                                                            <td valign="top" style="padding:6px 0; font-size:11px; color:#cbd5e1; font-family:'Courier New',Courier,monospace; word-break:break-all;">{{ $scan->sha256 }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" style="border-bottom:1px solid #334155; font-size:1px; height:1px;">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="110" valign="top" style="padding:6px 0; font-size:13px; color:#94a3b8;">Size</td>
                                                            <td valign="top" style="padding:6px 0; font-size:13px; color:#f1f5f9;">{{ number_format($scan->size_bytes / 1024, 2) }} KB</td>
                                                        </tr>
                                                        @if($scan->server_hostname)
                                                        <tr>
                                                            <td colspan="2" style="border-bottom:1px solid #334155; font-size:1px; height:1px;">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="110" valign="top" style="padding:6px 0; font-size:13px; color:#94a3b8;">Server</td>
                                                            <td valign="top" style="padding:6px 0; font-size:13px; color:#38bdf8; font-weight:bold;">{{ $scan->server_hostname }}</td>
                                                        </tr>
                                                        @endif
                                                        @if($scan->fullpath)
                                                        <tr>
                                                            <td colspan="2" style="border-bottom:1px solid #334155; font-size:1px; height:1px;">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="110" valign="top" style="padding:6px 0; font-size:13px; color:#94a3b8;">Full Path</td>
                                                            <td valign="top" style="padding:6px 0; font-size:11px; color:#cbd5e1; font-family:'Courier New',Courier,monospace; word-break:break-all;">{{ $scan->fullpath }}</td>
                                                        </tr>
                                                        @endif
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- ============================================ --}}
                            {{-- YARA MATCHES --}}
                            {{-- ============================================ --}}
                            @if(isset($scan->yara_result['matches']) && count($scan->yara_result['matches']) > 0)
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td bgcolor="#0f172a" style="background-color:#0f172a; padding:0 24px 16px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #334155;" bgcolor="#1e293b">
                                            <tr>
                                                <td bgcolor="#1e293b" style="background-color:#1e293b; padding:16px 18px;">
                                                    <p style="margin:0 0 10px 0; font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:2px; font-weight:bold;">
                                                        &#128269; YARA MATCHES ({{ count($scan->yara_result['matches']) }} RULES)
                                                    </p>
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                        @foreach($scan->yara_result['matches'] as $match)
                                                        <tr>
                                                            <td style="padding:3px 0;">
                                                                <!--[if mso]>
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                                                <td bgcolor="#422006" style="padding:4px 10px; font-size:11px; color:#fbbf24; font-family:'Courier New',Courier,monospace; border:1px solid #854d0e;">{{ $match['rule'] ?? 'unknown' }}</td>
                                                                </tr></table>
                                                                <![endif]-->
                                                                <!--[if !mso]><!-->
                                                                <span style="display:inline-block; padding:4px 10px; font-size:11px; font-family:'Courier New',Courier,monospace; background-color:#422006; color:#fbbf24; border:1px solid #854d0e; border-radius:3px; mso-hide:all;">{{ $match['rule'] ?? 'unknown' }}</span>
                                                                <!--<![endif]-->
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            {{-- ============================================ --}}
                            {{-- CLAMAV DETECTION --}}
                            {{-- ============================================ --}}
                            @if(isset($scan->clamav_result['infected']) && $scan->clamav_result['infected'] === true)
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td bgcolor="#0f172a" style="background-color:#0f172a; padding:0 24px 16px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #334155;" bgcolor="#1e293b">
                                            <tr>
                                                <td bgcolor="#1e293b" style="background-color:#1e293b; padding:16px 18px;">
                                                    <p style="margin:0 0 8px 0; font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:2px; font-weight:bold;">
                                                        &#128737; CLAMAV DETECTION
                                                    </p>
                                                    <p style="margin:0; font-size:13px; color:#f87171; font-family:'Courier New',Courier,monospace;">
                                                        {{ $scan->clamav_result['output'] ?? 'Virus Detected' }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            {{-- ============================================ --}}
                            {{-- ACTION BUTTON --}}
                            {{-- ============================================ --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td bgcolor="#0f172a" style="background-color:#0f172a; padding:10px 24px 28px;" align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ url('/webhook-scans/' . $scan->id) }}" style="height:44px;v-text-anchor:middle;width:220px;" arcsize="10%" stroke="f" fillcolor="#2563eb">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial;font-size:14px;font-weight:bold;">View Scan Report &#8594;</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <a href="{{ url('/webhook-scans/' . $scan->id) }}" target="_blank" style="display:inline-block; padding:12px 32px; background-color:#2563eb; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:bold; font-size:14px; font-family:Arial; mso-hide:all;">
                                            View Scan Report &#8594;
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- ============================================ --}}
                    {{-- FOOTER --}}
                    {{-- ============================================ --}}
                    <tr>
                        <td bgcolor="#1e293b" style="background-color:#1e293b; padding:16px 24px; border-top:1px solid #334155;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="font-size:11px; color:#64748b; line-height:18px;">
                                        SecOps Investigation Platform &bull; Mega Insurance<br/>
                                        Automated Threat Alert &bull; {{ $scan->created_at->format('d M Y, H:i:s') }} WIB
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                {{-- End main container --}}

            </td>
        </tr>
    </table>
    {{-- End background wrapper --}}

</body>
</html>
