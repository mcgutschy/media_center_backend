<?php
require_once "config.php";

$msg = "";
$msgType = "";
$networks = [];
$saved = [];
$scanStatus = "";
$connectStatus = "";

// ============================================================
// Aktionen verarbeiten
// ============================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    
    if ($action === "scan") {
        // Scan-Befehl an Pi senden: Task-Datei schreiben
        $task = ["action" => "scan", "timestamp" => date("c")];
        if (saveCommand($task)) {
            $msg = "Scan-Befehl gesendet! Der Pi prueft alle 30 Sekunden auf neue Befehle. Seite in ca. 1 Minute neu laden.";
            $msgType = "info";
        } else {
            $msg = "Fehler: Scan-Befehl konnte nicht geschrieben werden.";
            $msgType = "error";
        }
    }
    
    if ($action === "connect") {
        $ssid = trim($_POST["ssid"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $priority = intval($_POST["priority"] ?? 10);
        
        if ($ssid === "" || $password === "") {
            $msg = "SSID und Passwort sind Pflichtfelder!";
            $msgType = "error";
        } else {
            $task = [
                "action" => "connect",
                "ssid" => $ssid,
                "password" => $password,
                "priority" => $priority,
                "timestamp" => date("c")
            ];
            if (saveCommand($task)) {
                $msg = "Verbindungsbefehl fuer \"$ssid\" gesendet! Der Pi verbindet sich innerhalb von ca. 1 Minute.";
                $msgType = "info";
            } else {
                $msg = "Fehler: Verbindungsbefehl konnte nicht geschrieben werden.";
                $msgType = "error";
            }
        }
    }
    
    if ($action === "list_saved") {
        $task = ["action" => "list_saved", "timestamp" => date("c")];
        if (saveCommand($task)) {
            $msg = "Abfrage gesendet! Seite in ca. 1 Minute neu laden.";
            $msgType = "info";
        }
    }
    
    if ($action === "delete_saved") {
        $ssid = trim($_POST["ssid"] ?? "");
        if ($ssid !== "") {
            $task = [
                "action" => "delete_saved",
                "ssid" => $ssid,
                "timestamp" => date("c")
            ];
            if (saveCommand($task)) {
                $msg = "Loesch-Befehl fuer \"$ssid\" gesendet!";
                $msgType = "info";
            }
        }
    }
    
    if ($action === "clear_result") {
        @unlink(COMMANDS_DIR . "/wifi_result.json");
        $msg = "Ergebnis geloescht.";
        $msgType = "info";
    }
}

// ============================================================
// Ergebnis lesen
// ============================================================

$resultFile = COMMANDS_DIR . "/wifi_result.json";
if (file_exists($resultFile)) {
    $result = loadJson($resultFile);
    $status = $result["status"] ?? "";
    
    if ($status === "scan_complete") {
        $networks = $result["networks"] ?? [];
        $scanStatus = "success";
    } elseif ($status === "connect_success") {
        $connectStatus = "success";
        $msg = "Verbunden mit " . ($result["ssid"] ?? "?") . " — IP: " . ($result["ip"] ?? "?");
        $msgType = "success";
    } elseif ($status === "connect_failed") {
        $connectStatus = "failed";
        $msg = "Verbindung fehlgeschlagen: " . ($result["error"] ?? "Unbekannter Fehler");
        $msgType = "error";
    } elseif ($status === "saved_complete") {
        $saved = $result["networks"] ?? [];
    }
}

// Pruefe ob ein Task aussteht
$taskFile = COMMANDS_DIR . "/wifi_task.json";
$pendingTask = null;
if (file_exists($taskFile)) {
    $pendingTask = loadJson($taskFile);
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi-Verwaltung — Media Center Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0f0f0f; color: #e0e0e0;
            padding: 20px; max-width: 900px; margin: 0 auto;
        }
        a { color: #4fc3f7; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h1 { margin-bottom: 20px; font-size: 1.4em; }
        .back { margin-bottom: 20px; font-size: 0.9em; }
        
        .msg {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;
            font-size: 0.95em;
        }
        .msg.info { background: #1a3a5c; border-left: 4px solid #4fc3f7; }
        .msg.success { background: #1a3c1a; border-left: 4px solid #66bb6a; }
        .msg.error { background: #3c1a1a; border-left: 4px solid #ef5350; }
        
        .pending {
            background: #2a2a1a; border-left: 4px solid #ffa726;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        .section {
            background: #1a1a1a; border: 1px solid #333; border-radius: 10px;
            padding: 20px; margin-bottom: 20px;
        }
        .section h2 { margin-bottom: 15px; font-size: 1.1em; color: #4fc3f7; }
        
        .btn {
            display: inline-block; padding: 10px 20px; border: none;
            border-radius: 6px; cursor: pointer; font-size: 0.95em;
            transition: background 0.2s;
        }
        .btn-primary { background: #1565c0; color: white; }
        .btn-primary:hover { background: #1976d2; }
        .btn-success { background: #2e7d32; color: white; }
        .btn-success:hover { background: #388e3c; }
        .btn-danger { background: #c62828; color: white; }
        .btn-danger:hover { background: #d32f2f; }
        .btn-small { padding: 6px 12px; font-size: 0.85em; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #333; }
        th { color: #888; font-weight: 600; font-size: 0.85em; text-transform: uppercase; }
        td { font-size: 0.9em; }
        
        .signal { display: inline-block; width: 60px; }
        .signal-bar {
            display: inline-block; height: 8px; border-radius: 2px;
            margin-right: 6px; vertical-align: middle;
        }
        .signal-excellent { background: #66bb6a; }
        .signal-good { background: #ffa726; }
        .signal-weak { background: #ef5350; }
        
        .encrypted { color: #ffa726; }
        .open { color: #66bb6a; }
        
        .connect-form {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
            margin-top: 15px; padding-top: 15px; border-top: 1px solid #333;
        }
        .connect-form label { font-size: 0.85em; color: #888; display: block; margin-bottom: 4px; }
        .connect-form input {
            width: 100%; padding: 8px 12px; background: #0f0f0f;
            border: 1px solid #444; border-radius: 6px; color: #e0e0e0;
        }
        .connect-form input:focus { border-color: #4fc3f7; outline: none; }
        .connect-form .full-width { grid-column: 1 / -1; }
        
        .saved-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 0; border-bottom: 1px solid #333;
        }
        .saved-name { font-weight: 500; }
        .saved-priority { color: #888; font-size: 0.85em; }
        
        .auto-refresh { font-size: 0.8em; color: #666; margin-top: 10px; }
    </style>
    <script>
        // Auto-refresh wenn Task aussteht
        <?php if ($pendingTask !== null): ?>
        setTimeout(function() { location.reload(); }, 30000);
        <?php endif; ?>
    </script>
</head>
<body>

<div class="back"><a href="index.php">&larr; Zurueck zum Dashboard</a></div>

<h1>WiFi-Verwaltung</h1>

<?php if ($msg): ?>
<div class="msg <?= htmlspecialchars($msgType) ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($pendingTask !== null): ?>
<div class="pending">
    ⏳ Befehl ausstehend: <strong><?= htmlspecialchars($pendingTask["action"] ?? "?") ?></strong>
    — Der Pi prueft alle 30 Sek. Seite laedt automatisch neu...
</div>
<?php endif; ?>

<!-- Scan -->
<div class="section">
    <h2>📡 Verfuegbare Netzwerke</h2>
    
    <form method="POST" style="margin-bottom: 15px;">
        <input type="hidden" name="action" value="scan">
        <button type="submit" class="btn btn-primary">WLAN-Scan starten</button>
        <?php if (count($networks) > 0): ?>
        <button type="submit" name="action" value="clear_result" class="btn btn-small btn-danger" style="margin-left: 10px;">Ergebnis loeschen</button>
        <?php endif; ?>
    </form>
    
    <?php if (count($networks) > 0): ?>
    <table>
        <thead>
            <tr><th>SSID</th><th>Signal</th><th>Verschl.</th><th>Frequenz</th></tr>
        </thead>
        <tbody>
            <?php foreach ($networks as $net): ?>
            <tr>
                <td><strong><?= htmlspecialchars($net["ssid"] ?? "?") ?></strong></td>
                <td>
                    <?php
                    $sig = intval($net["signal"] ?? 0);
                    $barClass = $sig >= 70 ? "excellent" : ($sig >= 40 ? "good" : "weak");
                    $barWidth = max(10, min(100, $sig));
                    ?>
                    <span class="signal">
                        <span class="signal-bar signal-<?= $barClass ?>" style="width: <?= $barWidth ?>%"></span>
                        <?= $sig ?>%
                    </span>
                </td>
                <td>
                    <?php if (!empty($net["encrypted"])): ?>
                        <span class="encrypted">🔒 <?= htmlspecialchars($net["security"] ?? "WPA") ?></span>
                    <?php else: ?>
                        <span class="open">🔓 Offen</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($net["freq"] ?? "—") ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Connect-Formular -->
    <form method="POST" class="connect-form">
        <input type="hidden" name="action" value="connect">
        <div>
            <label for="ssid">Netzwerk (SSID)</label>
            <select name="ssid" id="ssid" required>
                <option value="">— Waehlen —</option>
                <?php foreach ($networks as $net): ?>
                <option value="<?= htmlspecialchars($net["ssid"] ?? "") ?>">
                    <?= htmlspecialchars($net["ssid"] ?? "") ?>
                    (<?= $net["signal"] ?? 0 ?>%)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="priority">Prioritaet (0-100, hoeher = bevorzugt)</label>
            <input type="number" id="priority" name="priority" value="10" min="0" max="100">
        </div>
        <div class="full-width">
            <label for="password">WLAN-Passwort</label>
            <input type="password" id="password" name="password" required placeholder="Passwort eingeben">
        </div>
        <div class="full-width">
            <button type="submit" class="btn btn-success">🔗 Verbinden</button>
        </div>
    </form>
    
    <?php elseif ($scanStatus === "success" && count($networks) === 0): ?>
    <p style="color: #888;">Keine Netzwerke gefunden.</p>
    <?php else: ?>
    <p style="color: #666;">Klicke auf "WLAN-Scan starten" um verfuegbare Netzwerke zu suchen. 
    Der Pi scannt und meldet die Ergebnisse innerhalb von ca. 1 Minute.</p>
    <?php endif; ?>
</div>

<!-- Gespeicherte Netzwerke -->
<div class="section">
    <h2>💾 Gespeicherte Netzwerke</h2>
    
    <form method="POST" style="margin-bottom: 15px;">
        <input type="hidden" name="action" value="list_saved">
        <button type="submit" class="btn btn-primary">Gespeicherte Netzwerke abfragen</button>
    </form>
    
    <?php if (count($saved) > 0): ?>
    <?php foreach ($saved as $net): ?>
    <div class="saved-item">
        <div>
            <span class="saved-name"><?= htmlspecialchars($net["name"] ?? "?") ?></span>
            <span class="saved-priority">(Prioritaet: <?= $net["priority"] ?? 0 ?>)</span>
        </div>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="delete_saved">
            <input type="hidden" name="ssid" value="<?= htmlspecialchars($net["name"] ?? "") ?>">
            <button type="submit" class="btn btn-small btn-danger">Loeschen</button>
        </form>
    </div>
    <?php endforeach; ?>
    <?php elseif (file_exists($resultFile) && ($result["status"] ?? "") === "saved_complete"): ?>
    <p style="color: #888;">Keine gespeicherten Netzwerke gefunden.</p>
    <?php else: ?>
    <p style="color: #666;">Klicke auf "Gespeicherte Netzwerke abfragen" um die Liste vom Pi zu holen.</p>
    <?php endif; ?>
</div>

<div class="auto-refresh">
    Hinweis: Der Pi prueft alle 30 Sekunden auf neue Befehle. 
    Nach dem Absenden eines Befehls laedt diese Seite automatisch neu.
</div>

</body>
</html>