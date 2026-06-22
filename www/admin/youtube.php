<?php
require_once "config.php";

$data = loadJson(YOUTUBE_FILE);
$channels = $data["channels"] ?? [];
$msg = "";
$msgType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "save") {
        $newChannels = [];
        $keys = $_POST["keys"] ?? "";
        $keyList = explode(",", $keys);
        foreach ($keyList as $key) {
            $key = trim($key);
            $name = trim($_POST["name_$key"] ?? "");
            $url = trim($_POST["url_$key"] ?? "");
            if ($name !== "" && $url !== "") {
                $newChannels[] = ["name" => $name, "url" => $url];
            }
        }
        $data["channels"] = $newChannels;
        if (saveJson(YOUTUBE_FILE, $data)) {
            $msg = "YouTube-Kanaele gespeichert"; $msgType = "ok";
            $channels = $newChannels;
        } else {
            $msg = "Fehler beim Speichern!"; $msgType = "err";
        }
    }
    elseif ($action === "add") {
        $channels[] = ["name" => "", "url" => ""];
        $msg = "Neuer Kanal — bitte Daten eintragen und speichern"; $msgType = "ok";
    }
    elseif ($action === "delete" && isset($_POST["delete_key"])) {
        $key = (int)$_POST["delete_key"];
        if (isset($channels[$key])) {
            array_splice($channels, $key, 1);
            $data["channels"] = $channels;
            if (saveJson(YOUTUBE_FILE, $data)) {
                $msg = "Kanal geloescht"; $msgType = "ok";
            }
        }
    }
    elseif ($action === "up" && isset($_POST["move_key"])) {
        $key = (int)$_POST["move_key"];
        if ($key > 0 && isset($channels[$key])) {
            $temp = $channels[$key];
            $channels[$key] = $channels[$key - 1];
            $channels[$key - 1] = $temp;
            $data["channels"] = $channels;
            if (saveJson(YOUTUBE_FILE, $data)) {
                $msg = "Kanal nach oben verschoben"; $msgType = "ok";
            }
        }
    }
    elseif ($action === "down" && isset($_POST["move_key"])) {
        $key = (int)$_POST["move_key"];
        if ($key < count($channels) - 1 && isset($channels[$key])) {
            $temp = $channels[$key];
            $channels[$key] = $channels[$key + 1];
            $channels[$key + 1] = $temp;
            $data["channels"] = $channels;
            if (saveJson(YOUTUBE_FILE, $data)) {
                $msg = "Kanal nach unten verschoben"; $msgType = "ok";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>YouTube-Kanaele</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#1a1a2e;color:#eee;min-height:100vh}
.container{max-width:800px;margin:0 auto;padding:1rem}
h1{color:#e94560;margin:1rem 0;font-size:1.4rem}
.nav{margin-bottom:1rem}.nav a{color:#e94560;text-decoration:none;font-size:0.9rem}
.msg{padding:0.8rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem}
.msg.ok{background:#0f3460;color:#4fc3f7}.msg.err{background:#3e1616;color:#e94560}
.channel{background:#16213e;border-radius:10px;padding:1rem 1rem 0.5rem;margin-bottom:0.4rem}
.channel-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem}
.channel-num{color:#e94560;font-weight:700;font-size:1.1rem}
.fields{display:grid;grid-template-columns:1fr 1fr;gap:0.5rem}
.field-full{grid-column:1/-1}
label{display:block;font-size:0.75rem;color:#888;margin-bottom:0.2rem}
input[type=text]{width:100%;padding:0.5rem;border:1px solid #333;border-radius:4px;background:#0f3460;color:#eee;font-size:0.9rem}
input[type=text]:focus{border-color:#e94560;outline:none}
.btn-bar{display:flex;gap:0.8rem;margin-top:1rem;flex-wrap:wrap}
.btn{padding:0.6rem 1.2rem;border:none;border-radius:6px;font-size:0.95rem;cursor:pointer;font-weight:600}
.btn-primary{background:#e94560;color:#fff}.btn-primary:hover{background:#c73e54}
.btn-secondary{background:#0f3460;color:#e94560;border:1px solid #333}.btn-secondary:hover{background:#1a4a8a}
.channel-footer{display:flex;gap:0.5rem;justify-content:flex-end;padding:0.5rem 0}
.sm-btn{background:none;border:1px solid #555;color:#aaa;padding:0.3rem 0.6rem;border-radius:4px;cursor:pointer;font-size:0.8rem}
.sm-btn:hover{background:#555;color:#fff}
.sm-btn.del{border-color:#e94560;color:#e94560}.sm-btn.del:hover{background:#e94560;color:#fff}
@media(max-width:600px){.fields{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="container">
<div class="nav"><a href="index.php">&larr; Zurueck</a></div>
<h1>YouTube-Kanaele (<?= count($channels) ?>)</h1>
<?php if ($msg): ?>
<div class="msg <?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="action" value="save">
<input type="hidden" name="keys" value="<?= implode(",", array_keys($channels)) ?>">
<?php foreach ($channels as $key => $ch): ?>
<div class="channel">
<div class="channel-header">
<span class="channel-num">#<?= $key ?></span>
</div>
<div class="fields">
<div><label>Name *</label><input type="text" name="name_<?= $key ?>" value="<?= htmlspecialchars($ch["name"] ?? "") ?>" required></div>
<div><label>Kanal-URL *</label><input type="text" name="url_<?= $key ?>" value="<?= htmlspecialchars($ch["url"] ?? "") ?>" required></div>
</div>
<div class="channel-footer">
<button type="submit" form="up_<?= $key ?>" class="sm-btn">&#9650; Hoch</button>
<button type="submit" form="down_<?= $key ?>" class="sm-btn">&#9660; Runter</button>
<button type="submit" form="del_<?= $key ?>" class="sm-btn del">Loeschen</button>
</div>
</div>
<?php endforeach; ?>
<div class="btn-bar"><button type="submit" class="btn btn-primary">Speichern</button></div>
</form>

<?php foreach ($channels as $key => $ch): ?>
<form id="del_<?= $key ?>" method="post" style="display:none"><input type="hidden" name="action" value="delete"><input type="hidden" name="delete_key" value="<?= $key ?>"></form>
<form id="up_<?= $key ?>" method="post" style="display:none"><input type="hidden" name="action" value="up"><input type="hidden" name="move_key" value="<?= $key ?>"></form>
<form id="down_<?= $key ?>" method="post" style="display:none"><input type="hidden" name="action" value="down"><input type="hidden" name="move_key" value="<?= $key ?>"></form>
<?php endforeach; ?>

<form method="post" style="margin-top:0.5rem">
<input type="hidden" name="action" value="add">
<button type="submit" class="btn btn-secondary">+ Neuer Kanal</button>
</form>
</div>
</body>
</html>
