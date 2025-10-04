<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>[GitHub] <?= $subject; ?></title>
    <!--[if mso]><style type="text/css">body, table, td {font-family: Arial, Helvetica, sans-serif !important;} a {text-decoration:none;}</style><![endif]-->
    <style>
        @media (prefers-color-scheme: dark) {
            .bg-body {
                background: #111827 !important;
            }

            .bg-card {
                background: #1f2937 !important;
                border-color: #374151 !important;
            }

            .text-main {
                color: #f1f5f9 !important;
            }

            .text-dim {
                color: #94a3b8 !important;
            }

            .badge {
                background: #3b82f6 !important;
                color: #fff !important;
            }
        }

        /* Mobile spacing refinement */
        @media only screen and (max-width:600px) {
            .p-sm {
                padding: 20px !important;
            }

            .h1 {
                font-size: 20px !important;
                line-height: 24px !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; background:#f0f2f5; font-family:Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">New contact form message received. &#8203;&#8203;&#8203;&#8203;&#8203;&#8203;</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f0f2f5;">
        <tr>
            <td align="center" style="padding:28px 12px;">

                <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background:#ffffff; border:1px solid #e5e7eb; border-radius:6px; overflow:hidden;" class="bg-card">
                    <tr>
                        <td align="left" style="background:#2563eb; padding:24px; font-size:20px; line-height:24px; font-weight:700; color:#ffffff; font-family:Arial,Helvetica,sans-serif;" class="h1">
                            <span class="badge" style="display:inline-block; background:#1d4ed8; color:#fff; padding:4px 10px; font-size:11px; line-height:1; letter-spacing:.5px; border-radius:3px; text-transform:uppercase;">Message</span><br style="line-height:20px;" />
                            <?= htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 28px 8px 28px; font-size:15px; line-height:1.55; color:#1f2937; font-family:Arial,Helvetica,sans-serif;" class="p-sm text-main">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <tbody>
                                    <tr>
                                        <td style="padding:0 0 18px 0; vertical-align:top;">
                                            <div style="font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 4px 0;">Date</div>
                                            <div style="font-size:15px; color:#111827; font-weight:500;"><?= $date; ?></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 18px 0; vertical-align:top;">
                                            <div style="font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 4px 0;">Name</div>
                                            <div style="font-size:15px; color:#111827; font-weight:500;"><?= $name; ?></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 18px 0; vertical-align:top; word-break:break-all;">
                                            <div style="font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 4px 0;">Email</div>
                                            <a href="mailto:<?= $email; ?>" style="font-size:15px; color:#2563eb; text-decoration:none;"><?= $email; ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0; vertical-align:top;">
                                            <div style="font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 8px 0;">Message</div>
                                            <div style="margin:0; font-size:15px; line-height:1.7; color:#111827; word-break:break-word;">
                                                <?= $message; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:24px 0 0 0;">
                                            <!-- Reply Button -->
                                            <?php $replySubject = rawurlencode('Re: ' . $subject); ?>
                                            <a href="mailto:<?= $email; ?>?subject=<?= $replySubject; ?>"
                                                style="display:inline-block; background:#2563eb; color:#ffffff; text-decoration:none; font-size:14px; line-height:18px; padding:12px 20px; border-radius:4px; font-weight:600; font-family:Arial,Helvetica,sans-serif;">
                                                Reply to <?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0 16px 0;">
                                <tr>
                                    <td style="border-top:1px solid #e5e7eb; font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:12px; color:#6b7280; line-height:1.4;">
                                        <strong style="color:#374151;">IP:</strong> <?= $ip; ?><br />
                                        <span style="color:#9ca3af;">This email was generated automatically</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background:#f9fafb; padding:18px; font-size:12px; line-height:18px; color:#6b7280; font-family:Arial,Helvetica,sans-serif;">
                            © <?= date('Y'); ?> Contact Form • Raspgot
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
    <div style="line-height:28px; font-size:28px;">&nbsp;</div>
</body>

</html>