<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title>[GITHUB] <?= $subject; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
</head>

<body style="margin:0; padding:0; background:#f0f2f5; font-family:Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <!-- Preheader -->
    <span style="display:none; font-size:1px; color:#ffffff; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all;">
        You've received a new message from the contact form. &#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;
    </span>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f0f2f5;">
        <tr>
            <td align="center" style="padding:24px 12px;">

                <!--[if mso]>
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td>
                <![endif]-->

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background:#ffffff; border:1px solid #e6e9ef;">
                    <tr>
                        <td bgcolor="#2563eb" align="left" style="padding:24px; color:#ffffff; font-size:20px; line-height:24px; font-weight:bold;">
                            [GITHUB] <?= $subject; ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left" style="padding:24px; color:#1f2937; font-size:15px; line-height:1.6;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:0 0 16px 0;">
                                        <div style="font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 4px 0;">Date</div>
                                        <div style="font-size:15px; color:#111827;"><?= $date; ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 16px 0;">
                                        <div style="font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 4px 0;">Name</div>
                                        <div style="font-size:15px; color:#111827;"><?= $name; ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 16px 0;">
                                        <div style="font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 4px 0;">Email</div>
                                        <a href="mailto:<?= $email; ?>" style="font-size:15px; color:#2563eb; text-decoration:none; word-break:break-all;"><?= $email; ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0;">
                                        <div style="font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; margin:0 0 8px 0;">Message</div>
                                        <div style="margin:0; font-size:15px; line-height:1.7; color:#111827;">
                                            <?= $message; ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
                                <tr>
                                    <td style="border-top:1px solid #e5e7eb; font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:13px; color:#6b7280;">
                                        <strong style="color:#374151;">IP address:</strong> <?= $ip; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td bgcolor="#f9fafb" align="center" style="padding:16px; font-size:12px; line-height:18px; color:#6b7280;">
                            This is an automated message.
                        </td>
                    </tr>
                </table>

                <!--[if mso]></td></tr></table><![endif]-->

                <div style="line-height:24px; font-size:24px;">&nbsp;</div>
            </td>
        </tr>
    </table>
</body>

</html>