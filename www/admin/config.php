<?php
// Konfiguration
define("DATA_DIR", "/data/linklists");
define("RADIO_FILE", DATA_DIR . "/radio/radio_stations.json");
define("YOUTUBE_FILE", DATA_DIR . "/youtube/youtube_channels.json");
define("ADMIN_USER", "admin");
define("ADMIN_PASS", "Strato2026!");

// Einfache HTTP-Auth
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
    // Radio: als Objekt mit String-Keys speichern
    // YouTube: channels-Array bleibt Array, Top-Level wird Objekt
    if (strpos($file, "radio_stations") !== false) {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT);
    } else {
        // YouTube und andere: Top-Level als Objekt, aber Arrays innerhalb bleiben Arrays
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if ($json === false) return false;
    return file_put_contents($file, $json . "\n") !== false;
}

// --- WiFi-Befehlssystem ---
define("COMMANDS_DIR", "/data/commands");

function saveCommand($task) {
    $json = json_encode($task, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    return file_put_contents(COMMANDS_DIR . "/wifi_task.json", $json . "\n") !== false;
}
