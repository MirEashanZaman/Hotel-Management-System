<?php

function sendHotelMail($to, $subject, $bodyHtml) {
    $mailDir = __DIR__ . '/../uploads/emails/';
    if (!is_dir($mailDir)) {
        mkdir($mailDir, 0777, true);
    }

    $time = date('Y-m-d H:i:s');
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $to) . '.html';
    $filePath = $mailDir . $fileName;

    $fullBodyHtml = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>" . htmlspecialchars($subject) . "</title>
        <style>
            body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; color: #333333; }
            .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #dddddd; padding: 40px; }
            .header { border-bottom: 2px solid #c9a84c; padding-bottom: 20px; margin-bottom: 30px; text-align: center; }
            .header h1 { color: #c9a84c; font-size: 24px; margin: 0; font-family: Georgia, serif; }
            .header p { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #777; margin: 5px 0 0 0; }
            .content { font-size: 14px; line-height: 1.6; color: #555555; }
            .footer { border-top: 1px solid #eeeeee; padding-top: 20px; margin-top: 30px; font-size: 11px; color: #999999; text-align: center; }
            .btn { display: inline-block; padding: 10px 20px; background-color: #c9a84c; color: #ffffff !important; text-decoration: none; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-top: 20px; letter-spacing: 1px; }
            .btn:hover { background-color: #a18332; }
        </style>
    </head>
    <body>
        <div style='background-color: #111111; color: #888888; font-size: 10px; padding: 10px 20px; font-family: monospace;'>
            [MAIL LOG] Date: {$time} | To: {$to} | Subject: {$subject} | File: uploads/emails/{$fileName}
        </div>
        <div class='container'>
            <div class='header'>
                <h1>Grand Palace Hotel</h1>
                <p>Luxury & Comfort Defined</p>
            </div>
            <div class='content'>
                {$bodyHtml}
            </div>
            <div class='footer'>
                This is a simulated transactional notification from Grand Palace Hotel System.<br>
                &copy; " . date('Y') . " Grand Palace Hotel. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ";

    return file_put_contents($filePath, $fullBodyHtml) !== false;
}
