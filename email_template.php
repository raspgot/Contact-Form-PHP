<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($subject); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial, sans-serif;">
    <span style="display:none; font-size:1px; color:#ffffff; line-height:1px; max-height:0px; max-width:0px; opacity:0; overflow:hidden; mso-hide:all;">
        You've received a new message via the contact form.
    </span>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4;">
        <tr>
            <td align="center" style="padding:20px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background:#ffffff; border:1px solid #e0e0e0;">
                    <tr>
                        <td align="center" style="background:#4a90e2; color:#ffffff; font-size:20px; font-weight:bold; padding:20px;">
                            <?php echo htmlspecialchars($subject); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px; font-size:16px; color:#333333; line-height:1.5;">
                            <p style="margin:0 0 16px;">
                                <strong>Date:</strong><br>
                                <?php echo htmlspecialchars($date); ?>
                            </p>
                            <p style="margin:0 0 16px;">
                                <strong>Name:</strong><br>
                                <?php echo htmlspecialchars($name); ?>
                            </p>
                            <p style="margin:0 0 16px;">
                                <strong>Email:</strong><br>
                                <a href="mailto:<?php echo htmlspecialchars($email); ?>" style="color:#4a90e2; text-decoration:none; word-break:break-all;">
                                    <?php echo htmlspecialchars($email); ?>
                                </a>
                            </p>
                            <p style="margin:0 0 16px;">
                                <strong>Message:</strong><br>
                                <?php echo nl2br(htmlspecialchars($message)); ?>
                            </p>
                            <hr style="border:0; border-top:1px solid #dddddd; margin:24px 0;">
                            <p style="font-size:14px; color:#666666; margin:0;">
                                <strong>IP address:</strong>
                                <?php echo htmlspecialchars($ip); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background:#f4f4f4; font-size:12px; color:#999999; padding:16px;">
                            This is an automated message.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>