<?php require_once "config.php"; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Center Demo - WiFi</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
               background: #1a1a2e; color: #eee; min-height: 100vh; }
        .container { max-width: 700px; margin: 0 auto; padding: 2rem; }
        .demo-banner { background: #ffa726; color: #1a1a2e; text-align: center; padding: 0.6rem;
                       border-radius: 8px; margin-bottom: 1.5rem; font-weight: 700;
                       font-size: 0.95rem; letter-spacing: 2px; }
        h1 { color: #e94560; margin-bottom: 1rem; }
        .notice { background: #16213e; border-radius: 12px; padding: 2rem;
                  text-align: center; border-left: 4px solid #ffa726; }
        .notice h2 { color: #ffa726; margin-bottom: 1rem; }
        .notice p { color: #aaa; margin-bottom: 0.5rem; }
        .back { text-align: center; margin-top: 2rem; }
        .back a { color: #e94560; text-decoration: none; }
        .back a:hover { text-decoration: underline; }
        .logout { text-align: center; margin-top: 1rem; }
        .logout a { color: #666; text-decoration: none; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="demo-banner">DEMO MODUS &mdash; Keine Verbindung zum Raspberry Pi</div>
        <h1>WiFi-Verwaltung</h1>
        <div class="notice">
            <h2>Nicht verf&uuml;gbar</h2>
            <p>Die WiFi-Verwaltung ist im Demo-Modus deaktiviert.</p>
            <p>Sie ben&ouml;tigt eine Verbindung zum Raspberry Pi, um Netzwerke zu scannen und zu konfigurieren.</p>
        </div>
        <div class="back">
            <a href="index.php">&larr; Zur&uuml;ck zum Dashboard</a>
        </div>
        <div class="logout">
            <a href="logout.php">Abmelden</a>
        </div>
    </div>
</body>
</html>
