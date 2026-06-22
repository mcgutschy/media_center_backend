<?php require_once "config.php"; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Center Demo - Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
               background: #1a1a2e; color: #eee; min-height: 100vh; }
        .container { max-width: 700px; margin: 0 auto; padding: 2rem; }
        .demo-banner { background: #ffa726; color: #1a1a2e; text-align: center; padding: 0.6rem;
                       border-radius: 8px; margin-bottom: 1.5rem; font-weight: 700;
                       font-size: 0.95rem; letter-spacing: 2px; }
        h1 { color: #e94560; margin-bottom: 2rem; font-size: 1.6rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
        .card { background: #16213e; border-radius: 12px; padding: 1.5rem;
                box-shadow: 0 4px 16px rgba(0,0,0,0.2);
                text-decoration: none; display: block; transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .card h2 { color: #e94560; margin-bottom: 0.5rem; font-size: 1.2rem; }
        .card h3 { color: #ffa726; margin-bottom: 0.5rem; font-size: 1.1rem; }
        .card p { color: #aaa; font-size: 0.85rem; }
        .card .count { float: right; background: #0f3460; color: #e94560;
                       padding: 0.3rem 0.8rem; border-radius: 20px; font-weight: 600;
                       font-size: 0.85rem; }
        .card.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
        .hoerbuch-card { border-left: 4px solid #4a9eff; }
        .radio-card { border-left: 4px solid #e94560; }
        .youtube-card { border-left: 4px solid #ff0000; }
        .wifi-card { border-left: 4px solid #ffa726; }
        .info { background: #0f3460; border-radius: 8px; padding: 1rem;
                margin-bottom: 1.5rem; font-size: 0.85rem; color: #aaa; line-height: 1.6; }
        .logout { text-align: center; margin-top: 2rem; }
        .logout a { color: #666; text-decoration: none; font-size: 0.85rem; }
        .logout a:hover { color: #e94560; }
    </style>
</head>
<body>
    <div class="container">
        <div class="demo-banner">DEMO MODUS &mdash; Keine Verbindung zum Raspberry Pi</div>
        <h1>Media Center Demo</h1>
        <div class="info">
            Dies ist eine Demo-Instanz des Admin-Panels. &Auml;nderungen werden
            lokal gespeichert, aber <strong>nicht</strong> auf einen Raspberry Pi
            &uuml;bertragen. Die WiFi-Verwaltung ist deaktiviert.
        </div>
        <?php
            $radio = loadJson(RADIO_FILE);
            $youtube = loadJson(YOUTUBE_FILE);
            $radioCount = count($radio);
            $ytCount = count($youtube["channels"] ?? []);
        ?>
        <div class="grid">
            <a href="https://media.b481.de/files/media" target="_blank" class="card hoerbuch-card">
                <span class="count"><?= $audioCount ?> Dateien</span>
                <h2>H&ouml;rb&uuml;cher</h2>
                <p>Upload / Download (FileBrowser)</p>
            </a>

            <a href="radio.php" class="card radio-card">
                <span class="count"><?= $radioCount ?> Stationen</span>
                <h2>Radio-Stationen</h2>
                <p>Name, Stream-URL, Genre</p>
            </a>
            <a href="youtube.php" class="card youtube-card">
                <span class="count"><?= $ytCount ?> Kan&auml;le</span>
                <h2>YouTube-Kan&auml;le</h2>
                <p>Name, Kanal-URL</p>
            </a>
            <div class="card wifi-card disabled">
                <h3>WiFi-Verwaltung</h3>
                <p>Im Demo-Modus nicht verf&uuml;gbar</p>
            </div>
        </div>
        <div class="logout">
            <a href="logout.php">Abmelden</a>
        </div>
    </div>
</body>
</html>
