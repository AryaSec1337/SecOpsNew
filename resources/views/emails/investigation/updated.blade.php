<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Investigation Updated</title>
    <style>
        body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        p { display: block; margin: 0; }

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
                                            @if($newStatus === 'Resolved')
                                                <h1 style="margin: 0; color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.2;">Investigation Resolved</h1>
                                                <p style="margin-top: 8px; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.5;">The following investigation has been completed and closed.</p>
                                            @else
                                                <h1 style="margin: 0; color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 1.2;">Status Updated</h1>
                                                <p style="margin-top: 8px; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.5;">The status of an active investigation has changed.</p>
                                            @endif
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

                                    <!-- Row 2: Description -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Description</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #475569; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5;">{{ Illuminate\Support\Str::limit($mitigationLog->description, 300) }}</p>
                                        </td>
                                    </tr>

                                    <!-- Row 3: Type -->
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

                                    <!-- Row 4: Reported By -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Reported By</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">
                                                <a href="mailto:{{ $mitigationLog->reporter_email }}" style="color: #3b82f6; text-decoration: none;">{{ $mitigationLog->reporter_email ?? 'N/A' }}</a>
                                            </p>
                                            @if($department)
                                                <p style="margin-top: 4px; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px;">
                                                    <span style="background-color: #ecfdf5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">{{ $department }}</span>
                                                </p>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Row 5: Updated By -->
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Updated By</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px;">{{ Auth::user()->name ?? 'System' }}</p>
                                        </td>
                                    </tr>

                                    <!-- Row 6: Classification (only when Resolved) -->
                                    @if($newStatus === 'Resolved')
                                    <tr>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            <p style="color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;">Classification</p>
                                        </td>
                                        <td valign="top" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                            @if($mitigationLog->attack_classification === 'True Attack')
                                                <span style="background-color: #fee2e2; color: #991b1b; padding: 5px 12px; border-radius: 4px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 700;">&#9888; TRUE ATTACK</span>
                                            @elseif($mitigationLog->attack_classification === 'False Attack')
                                                <span style="background-color: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 4px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 700;">&#10003; FALSE ATTACK</span>
                                            @else
                                                <span style="background-color: #f1f5f9; color: #475569; padding: 5px 12px; border-radius: 4px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 600;">Not Classified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </table>

                                <!-- Status Visual -->
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 30px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                    <tr>
                                        <td width="50%" align="center" style="padding: 20px; border-right: 1px solid #e2e8f0;">
                                            <p style="margin-bottom: 5px; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase;">Previous</p>
                                            <p style="margin: 0; color: #94a3b8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 500;">{{ $oldStatus }}</p>
                                        </td>
                                        <td width="50%" align="center" style="padding: 20px;">
                                            <p style="margin-bottom: 5px; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase;">Current Status</p>
                                            @php
                                                $statusColor = match($newStatus) {
                                                    'Pending' => '#f59e0b',
                                                    'In Progress' => '#3b82f6',
                                                    'Resolved' => '#10b981',
                                                    default => '#64748b',
                                                };
                                            @endphp
                                            <p style="margin: 0; color: {{ $statusColor }}; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700;">{{ $newStatus }}</p>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Issue Timeline (only when Resolved) -->
                                @if($newStatus === 'Resolved' && count($timeline) > 0)
                                <div style="margin-top: 30px;">
                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="padding-bottom: 12px;">
                                                <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 700;">Issue Timeline</p>
                                            </td>
                                        </tr>
                                    </table>

                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                        @foreach($timeline as $index => $entry)
                                        <tr>
                                            <td style="padding: 12px 16px; {{ $index < count($timeline) - 1 ? 'border-bottom: 1px solid #e2e8f0;' : '' }}">
                                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td width="24" valign="top" style="padding-right: 10px;">
                                                            @if($index === count($timeline) - 1 && $newStatus === 'Resolved')
                                                                <div style="width: 20px; height: 20px; border-radius: 50%; background-color: #10b981; text-align: center; line-height: 20px; color: #ffffff; font-size: 11px;">&#10003;</div>
                                                            @else
                                                                <div style="width: 20px; height: 20px; border-radius: 50%; background-color: #e2e8f0; text-align: center; line-height: 20px; color: #64748b; font-size: 10px;">{{ $index + 1 }}</div>
                                                            @endif
                                                        </td>
                                                        <td valign="top">
                                                            <p style="color: #0f172a; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 600;">{{ $entry['action'] }}</p>
                                                        </td>
                                                        <td width="140" valign="top" align="right">
                                                            <p style="color: #94a3b8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px;">{{ $entry['date']->timezone('Asia/Jakarta')->format('M d, H:i') }} WIB</p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                                @endif

                                <!-- PDF Note (only when Resolved) -->
                                @if($newStatus === 'Resolved')
                                <div style="margin-top: 25px; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 16px;">
                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td width="36" valign="top">
                                                <div style="width: 28px; height: 28px; background-color: #d1fae5; border-radius: 8px; text-align: center; line-height: 28px; font-size: 15px;">&#128206;</div>
                                            </td>
                                            <td valign="top" style="padding-left: 8px;">
                                                <p style="color: #065f46; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 700;">PDF Report Attached</p>
                                                <p style="color: #047857; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; margin-top: 3px;">The full investigation report has been attached to this email for your records.</p>
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
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ route('mitigation-logs.show', $mitigationLog->id) }}" style="height:50px;v-text-anchor:middle;width:240px;" arcsize="8%" stroke="f" fillcolor="#0f172a">
                                            <w:anchorlock/>
                                            <center>
                                            <![endif]-->
                                            <a href="{{ route('mitigation-logs.show', $mitigationLog->id) }}" style="background-color:#0f172a; border-radius:6px; color:#ffffff; display:inline-block; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:16px; font-weight:600; line-height:50px; text-align:center; text-decoration:none; width:240px; box-shadow: 0 4px 6px rgba(15, 23, 42, 0.25);">View Investigation Log</a>
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
