<?php
// Demo-Modus Konfiguration
define("DEMO_MODE", true);
define("WIFI_ENABLED", false);

define("DATA_DIR", "/data/demo/linklists");
define("RADIO_FILE", DATA_DIR . "/radio/radio_stations.json");
define("YOUTUBE_FILE", DATA_DIR . "/youtube/youtube_channels.json");
define("ADMIN_USER", "demo");
define("ADMIN_PASS", "demo2026");

// HTTP-Auth
session_start();
if (!isset($_SESSION["auth"]) && basename($_SERVER["PHP_SELF"]) !== "login.php") {
    header("Location: login.php");
    exit;
}

function loadJson($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function saveJson($file, $data) {
    if (strpos($file, "radio_stations") !== false) {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT);
    } else {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if ($json === false) return false;
    return file_put_contents($file, $json . "\n") !== false;
}

// WiFi-Commands im Demo-Modus deaktiviert
function saveCommand($task) {
    return false; // Demo: kein WiFi
}
