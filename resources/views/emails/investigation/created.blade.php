<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>New Investigation Log</title>
    <style>
        body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        p { display: block; margin: 0; }
        
        /* Typography & Colors */
        .text-dark { color: #1e293b; }
        .text-light { color: #64748b; }
        .text-accent { color: #3b82f6; }
        .bg-body { background-color: #f1f5f9; }
        .bg-card { background-color: #ffffff; }

        @media only screen and (max-width: 600px) {
            .mobile-wrapper { width: 100% !important; max-width: 100% !important; }
            .mobile-padding { padding: 20px !important; }
            .stack-column { display: block !important; width: 100% !important; max-width: 100% !important; padding-bottom: 10px; }
        }
    </style>
    <!--[if mso]>
    <xml>
    <o:OfficeDocumentSettings>
        <o:AllowPNG/>
        <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
</head>
<body class="bg-body" style="margin:0; padding:0; background-color:#f1f5f9;">
    <center>
        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f1f5f9;">
            <tr>
                <td align="center" style="padding: 40px 0;">
                    
                    <!-- Main Card -->
                    <table role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" class="mobile-wrapper" style="width:600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden;">
                        
                        <!-- Premium Header -->
                        <tr>
                            <td bgcolor="#0f172a" style="padding: 35px 40px; text-align: center;">
                                <img src="{{ $message->embed(public_path('images/mega-insurance-logo-white.png')) }}" alt="Mega Insurance" width="160" style="display:block; border:0; margin: 0 auto;">
                                <p style="margin-top: 15px; color: #94a3b8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;">Security Operations Investigation</p>
                            </td>
                        </tr>

                        <!-- Body Content -->
                        <tr>
                            <td class="mobile-padding" style="padding: 40px;">
                                
                                <!-- Headline -->
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 30px;">
                                    <tr>
                                        <td align="center">
                                            <h1 style="margin: 0; color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.2;">New Incident Report</h1>
                                            <p style="margin-top: 8px; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.5;">A new investigation has been logged and is awaiting review.</p>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Details Grid -->
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="border-top: 1px solid #e2e8f0;">
                                    
                                    <!-- Row 1: Title -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0; width: 30%;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Title</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0; width: 70%;">
                                            <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 500;">{{ $mitigationLog->title }}</p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Row 2: Type -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Incident Type</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                             @if($mitigationLog->type === 'Email Phishing')
                                                <span style="background-color: #fef3c7; color: #b45309; padding: 4px 8px; border-radius: 4px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600;">Email Phishing</span>
                                            @else
                                                <span style="background-color: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600;">{{ $mitigationLog->type ?? 'General Incident' }}</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Row 3: Reported By -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Reported By</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px;">
                                                <a href="mailto:{{ $mitigationLog->reporter_email }}" style="color: #3b82f6; text-decoration: none;">{{ $mitigationLog->reporter_email ?? 'N/A' }}</a>
                                            </p>
                                        </td>
                                    </tr>

                                    <!-- Row 4: Department (Hidden for General) -->
                                    @if($mitigationLog->type !== 'General')
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Department</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            @if($department)
                                                <span style="background-color: #ecfdf5; color: #065f46; padding: 4px 10px; border-radius: 4px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 600;">{{ $department }}</span>
                                            @else
                                                <p style="color: #94a3b8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-style: italic;">Not assigned</p>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif

                                    <!-- Row 5: Incident Time -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Incident Time</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px;">{{ $mitigationLog->mitigated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                                        </td>
                                    </tr>

                                    <!-- Row 6: Status -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Status</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            @php
                                                $statusColor = match($mitigationLog->status) {
                                                    'Pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                                    'In Progress' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                                                    'Resolved' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                                    default => ['bg' => '#f1f5f9', 'text' => '#475569'],
                                                };
                                            @endphp
                                            <span style="background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }}; padding: 4px 10px; border-radius: 4px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600;">{{ $mitigationLog->status }}</span>
                                        </td>
                                        </td>
                                    </tr>

                                    <!-- Row 6.1: Priority & Severity -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Priority / Severity</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <span style="color: #0f172a; font-weight: 600; font-size: 14px;">{{ $mitigationLog->priority ?? '-' }}</span>
                                            <span style="color: #cbd5e1; margin: 0 5px;">/</span>
                                            <span style="color: #0f172a; font-weight: 600; font-size: 14px;">{{ $mitigationLog->severity ?? '-' }}</span>
                                        </td>
                                    </tr>

                                    <!-- Row 7: Logged By & Date -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Logged By</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px;">{{ $mitigationLog->user->name }} &bull; <span style="color: #64748b; font-size: 13px;">{{ $mitigationLog->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span></p>
                                        </td>
                                    </tr>

                                </table>

                                <!-- Phishing Visual Panel -->
                                @if($mitigationLog->type === 'Email Phishing')
                                <div style="margin-top: 30px; background-color: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; overflow: hidden;">
                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td bgcolor="#ffedd5" style="padding: 10px 20px; border-bottom: 1px solid #fed7aa;">
                                                <p style="color: #9a3412; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Phishing Analysis Details</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 20px;">
                                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td width="80" valign="top" style="padding-bottom: 10px; color: #9a3412; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600;">Subject</td>
                                                        <td valign="top" style="padding-bottom: 10px; color: #1e293b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">{{ $mitigationLog->email_subject }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="80" valign="top" style="padding-bottom: 10px; color: #9a3412; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600;">Sender</td>
                                                        <td valign="top" style="padding-bottom: 10px; color: #ef4444; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 500;">{{ $mitigationLog->email_sender }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="80" valign="top" style="color: #9a3412; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600;">To</td>
                                                        <td valign="top" style="color: #1e293b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">{{ $mitigationLog->email_recipient }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                @elseif($mitigationLog->type === 'General' && ($mitigationLog->hostname || $mitigationLog->internal_ip))
                                <div style="margin-top: 30px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                         <tr>
                                            <td bgcolor="#f1f5f9" style="padding: 10px 20px; border-bottom: 1px solid #e2e8f0;">
                                                <p style="color: #475569; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Affected Asset Details</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 20px;">
                                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td width="35%" valign="top" style="padding-bottom: 8px; color: #64748b; font-size: 12px; font-weight: 600;">Hostname</td>
                                                        <td valign="top" style="padding-bottom: 8px; color: #0f172a; font-size: 13px; font-weight: 500;">{{ $mitigationLog->hostname ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%" valign="top" style="padding-bottom: 8px; color: #64748b; font-size: 12px; font-weight: 600;">Internal IP</td>
                                                        <td valign="top" style="padding-bottom: 8px; color: #0f172a; font-size: 13px; font-family: monospace;">{{ $mitigationLog->internal_ip ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%" valign="top" style="padding-bottom: 8px; color: #64748b; font-size: 12px; font-weight: 600;">OS</td>
                                                        <td valign="top" style="padding-bottom: 8px; color: #0f172a; font-size: 13px;">{{ $mitigationLog->os ?? '-' }}</td>
                                                    </tr>
                                                     <tr>
                                                        <td width="35%" valign="top" style="color: #64748b; font-size: 12px; font-weight: 600;">Zone</td>
                                                        <td valign="top" style="color: #0f172a; font-size: 13px;">{{ $mitigationLog->network_zone ?? '-' }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                @endif

                                <!-- CTA Button -->
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 40px;">
                                    <tr>
                                        <td align="center">
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ route('mitigation-logs.show', $mitigationLog->id) }}" style="height:50px;v-text-anchor:middle;width:240px;" arcsize="8%" stroke="f" fillcolor="#3b82f6">
                                            <w:anchorlock/>
                                            <center>
                                            <![endif]-->
                                            <a href="{{ route('mitigation-logs.show', $mitigationLog->id) }}" style="background-color:#3b82f6; border-radius:6px; color:#ffffff; display:inline-block; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:16px; font-weight:600; line-height:50px; text-align:center; text-decoration:none; width:240px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25);">View Investigation</a>
                                            <!--[if mso]>
                                            </center>
                                            </v:roundrect>
                                            <![endif]-->
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td bgcolor="#f8fafc" style="padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                                <p style="color: #94a3b8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.5;">
                                    &copy; {{ date('Y') }} Mega Insurance Security Operations.<br>
                                    Jakarta, Indonesia
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p style="margin-top: 20px; color: #cbd5e1; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-align: center;">CONFIDENTIALITY NOTICE: The contents of this email message and any attachments are intended solely for the addressee(s) and may contain confidential and/or privileged information and may be legally protected from disclosure.</p>

                </td>
            </tr>
        </table>
    </center>
</body>
</html>
