<?php
require_once "config.php";

$radio = loadJson(RADIO_FILE);
$msg = "";
$msgType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "save") {
        $stations = [];
        $keys = $_POST["keys"] ?? "";
        $keyList = explode(",", $keys);
        foreach ($keyList as $key) {
            $key = trim($key);
            $name = trim($_POST["name_$key"] ?? "");
            $url = trim($_POST["url_$key"] ?? "");
            $genre = trim($_POST["genre_$key"] ?? "");
            $format = trim($_POST["format_$key"] ?? "MP3");
            $note = trim($_POST["note_$key"] ?? "");
            if ($name !== "" && $url !== "") {
                $stations[$key] = ["name" => $name, "url" => $url, "genre" => $genre, "format" => $format];
                if ($note !== "") { $stations[$key]["note"] = $note; }
            }
        }
        if (saveJson(RADIO_FILE, $stations)) {
            $msg = "Radio-Stationen gespeichert"; $msgType = "ok"; $radio = $stations;
        } else {
            $msg = "Fehler beim Speichern!"; $msgType = "err";
        }
    }
    elseif ($action === "add") {
        $nextKey = (string)count($radio);
        $radio[$nextKey] = ["name" => "", "url" => "", "genre" => "", "format" => "MP3"];
        $msg = "Neue Station — bitte Daten eintragen und speichern"; $msgType = "ok";
    }
    elseif ($action === "delete" && isset($_POST["delete_key"])) {
        $key = $_POST["delete_key"];
        unset($radio[$key]);
        $reindexed = []; $i = 0;
        foreach ($radio as $s) { $reindexed[(string)$i] = $s; $i++; }
        if (saveJson(RADIO_FILE, $reindexed)) {
            $msg = "Station geloescht"; $msgType = "ok"; $radio = $reindexed;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Radio-Stationen</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#1a1a2e;color:#eee;min-height:100vh}
.container{max-width:800px;margin:0 auto;padding:1rem}
h1{color:#e94560;margin:1rem 0;font-size:1.4rem}
.nav{margin-bottom:1rem}.nav a{color:#e94560;text-decoration:none;font-size:0.9rem}
.msg{padding:0.8rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem}
.msg.ok{background:#0f3460;color:#4fc3f7}.msg.err{background:#3e1616;color:#e94560}
.station{background:#16213e;border-radius:10px;padding:1rem 1rem 0.5rem;margin-bottom:0.4rem}
.station-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem}
.station-num{color:#e94560;font-weight:700;font-size:1.1rem}
.fields{display:grid;grid-template-columns:1fr 1fr;gap:0.5rem}
.field-full{grid-column:1/-1}
label{display:block;font-size:0.75rem;color:#888;margin-bottom:0.2rem}
input[type=text]{width:100%;padding:0.5rem;border:1px solid #333;border-radius:4px;background:#0f3460;color:#eee;font-size:0.9rem}
input[type=text]:focus{border-color:#e94560;outline:none}
.btn-bar{display:flex;gap:0.8rem;margin-top:1rem;flex-wrap:wrap}
.btn{padding:0.6rem 1.2rem;border:none;border-radius:6px;font-size:0.95rem;cursor:pointer;font-weight:600}
.btn-primary{background:#e94560;color:#fff}.btn-primary:hover{background:#c73e54}
.btn-secondary{background:#0f3460;color:#e94560;border:1px solid #333}.btn-secondary:hover{background:#1a4a8a}
.station-footer{display:flex;justify-content:flex-end;padding:0.5rem 0}
.delete-btn{background:none;border:1px solid #e94560;color:#e94560;padding:0.3rem 0.8rem;border-radius:4px;cursor:pointer;font-size:0.8rem}
.delete-btn:hover{background:#e94560;color:#fff}
@media(max-width:600px){.fields{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="container">
<div class="nav"><a href="index.php">&larr; Zurueck</a></div>
<h1>Radio-Stationen (<?= count($radio) ?>)</h1>
<?php if ($msg): ?>
<div class="msg <?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="action" value="save">
<input type="hidden" name="keys" value="<?= implode(",", array_keys($radio)) ?>">
<?php foreach ($radio as $key => $station): ?>
<div class="station">
<div class="station-header">
<span class="station-num">#<?= $key ?></span>
</div>
<div class="fields">
<div><label>Name *</label><input type="text" name="name_<?= $key ?>" value="<?= htmlspecialchars($station["name"] ?? "") ?>" required></div>
<div><label>Genre</label><input type="text" name="genre_<?= $key ?>" value="<?= htmlspecialchars($station["genre"] ?? "") ?>"></div>
<div class="field-full"><label>Stream-URL *</label><input type="text" name="url_<?= $key ?>" value="<?= htmlspecialchars($station["url"] ?? "") ?>" required></div>
<div><label>Format</label><input type="text" name="format_<?= $key ?>" value="<?= htmlspecialchars($station["format"] ?? "MP3") ?>"></div>
<div><label>Notiz</label><input type="text" name="note_<?= $key ?>" value="<?= htmlspecialchars($station["note"] ?? "") ?>"></div>
</div>
<div class="station-footer">
<button type="submit" name="action" value="delete" form="del_<?= $key ?>" class="delete-btn">Loeschen</button>
</div>
</div>
<?php endforeach; ?>
<div class="btn-bar"><button type="submit" class="btn btn-primary">Speichern</button></div>
</form>

<?php foreach ($radio as $key => $station): ?>
<form id="del_<?= $key ?>" method="post" style="display:none">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="delete_key" value="<?= $key ?>">
</form>
<?php endforeach; ?>

<form method="post" style="margin-top:0.5rem">
<input type="hidden" name="action" value="add">
<button type="submit" class="btn btn-secondary">+ Neue Station</button>
</form>
</div>
</body>
</html>
