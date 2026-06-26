# Media Center — Backend Server

Server-seitige Infrastruktur für das [Media Center](https://b481.de/media-center/) — ein headless Internetradio für blinde Nutzer, gebaut mit Raspberry Pi und Arduino.

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Debian](https://img.shields.io/badge/Debian-13-red.svg)](https://debian.org)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)](https://php.net)
[![Nginx](https://img.shields.io/badge/Nginx-1.26-green.svg)](https://nginx.org)

---

## Übersicht

Der Backend-Server ist die Online-Schaltzentrale des Media Centers. Er verwaltet
Radiostationen, YouTube-Kanäle und Hörbücher über ein Web-Interface und
ermöglicht die Fernsteuerung des Raspberry Pi — selbst wenn der Pi hinter
wechselnden WLAN-Netzen steht.

**Client-Repo:** [media_center_final](https://github.com/mcgutschy/media_center_final)

---

## Architektur

```
Internet
  │
  ▼
┌──────────────────────────────────────┐
│         Nginx (Port 443)             │
│  Reverse Proxy · Let's Encrypt TLS   │
│  PHP-FPM (Unix Socket)               │
├──────────────────────────────────────┤
│                                      │
│  ┌─────────────────┐  ┌───────────┐ │
│  │   FileBrowser    │  │ Admin-Panel│ │
│  │ (127.0.0.1:8080)│  │ /admin/    │ │
│  │                 │  │ /demo/     │ │
│  │  Web-UI für     │  │ PHP 8.4    │ │
│  │  Datei-Upload   │  │ Session    │ │
│  └────────┬────────┘  └─────┬─────┘ │
│           │                 │       │
│  ┌────────┴─────────────────┴─────┐ │
│  │          /data/                │ │
│  │  /media/       MP3-Hörbücher   │ │
│  │  /linklists/   JSON-Konfig     │ │
│  │  /commands/    WiFi-Tasks      │ │
│  └────────────────────────────────┘ │
└──────────────────────────────────────┘
           │
           ▼
    Raspberry Pi (Pull-Sync alle 10 Min)
```

## Komponenten

### FileBrowser (v2.32.0)
Single-Binary-Dateimanager, läuft als systemd-Service auf `127.0.0.1:8080`.
Bietet Web-UI für Datei-Uploads und eine REST-API für den automatischen Sync.

- **User:** `admin` (Vollzugriff), `pi-sync` (nur Lesen/Download)
- **Auth:** JSON-basiert mit JWT-Token (`X-Auth`-Header)
- **DB:** SQLite (`/var/lib/filebrowser/filebrowser.db`)

### Admin-Panel (PHP 8.4)
Session-basierte Webanwendung zum Bearbeiten der Radio- und YouTube-Konfiguration.

| Seite | Funktion |
|---|---|
| `login.php` | Session-Login |
| `index.php` | Dashboard (Anzahl Stationen/Kanäle, Hörbücher) |
| `radio.php` | CRUD für Radiostationen (Name, Stream-URL, Genre, Format) |
| `youtube.php` | CRUD für YouTube-Kanäle (Name, Kanal-URL, Reihenfolge) |
| `wifi.php` | WLAN-Scan und Verbindungsaufbau aus der Ferne |
| `logout.php` | Session beenden |

**JSON-Validierung:** Atomares Schreiben (temp + rename) verhindert korrupte
Daten. Radio-JSON verwendet `JSON_FORCE_OBJECT` für stabile Schlüsselstruktur.

### Demo-Modus (`/demo/`)
Vollständig isolierte Demo-Instanz mit eigenen Datenverzeichnissen.
Login vorausgefüllt (`demo`/`demo2026`), WiFi-Funktionen deaktiviert.

### WiFi-Agent (Poll-basiert)
Da der Raspberry Pi keine feste öffentliche IP hat, initiiert er alle 30 Sekunden
eine Verbindung zum Server und prüft auf ausstehende Kommandos:

```
Admin-Panel → /data/commands/wifi_task.json
                  ↓ (Pi pollt alle 30s)
             Pi führt aus (nmcli scan/connect)
                  ↓
             /data/commands/wifi_result.json → Admin-Panel
```

Unterstützt WPA2 und offene Netzwerke, Verbindungsprioritäten und gespeicherte Profile.

### Pull-Sync (Cron, alle 10 Min)
Der Pi synchronisiert Hörbücher und JSON-Konfiguration per FileBrowser-REST-API:

```
Login (JWT) → Verzeichnis abrufen → Neue/geänderte Dateien downloaden
```

Vergleich erfolgt per Dateigröße und lokalem State-File (`.sync-state/`).

## Verzeichnisstruktur

```
/etc/
├── filebrowser/config.json              ← FileBrowser-Konfiguration
├── nginx/sites-available/media-center   ← Reverse Proxy + PHP-FPM
└── systemd/system/filebrowser.service   ← systemd-Unit (auto-restart)

/var/www/
├── media-admin/                         ← Admin-Panel (Live)
│   ├── config.php                       ← Datenpfade, Helper-Funktionen
│   ├── index.php, login.php, logout.php
│   ├── radio.php, youtube.php, wifi.php
└── media-demo/                          ← Demo-Instanz (isoliert)

/data/
├── media/                               ← MP3-Hörbücher
├── linklists/
│   ├── radio/radio_stations.json        ← 11 Stationen
│   └── youtube/youtube_channels.json    ← 11 Kanäle
└── commands/                            ← WiFi-Task/Result-JSONs
```

## API-Referenz (FileBrowser)

| Methode | Endpunkt | Zweck |
|---|---|---|
| POST | `/api/login` | JWT-Token holen (Body: `{"username":"...","password":"..."}`) |
| GET | `/api/resources/pfad` | Verzeichnisinhalt auflisten |
| GET | `/api/raw/pfad` | Datei herunterladen |
| POST | `/api/resources/pfad` | Datei hochladen (JSON-Content-Type) |

**Auth-Header:** `X-Auth: <jwt-token>` (nicht `Authorization: Bearer`!)

## Setup (neuer Server)

1. **Debian 13 Grundsystem** — Pakete: `nginx`, `certbot`, `python3-certbot-nginx`, `php-fpm`, `php-json`, `ufw`, `git`, `jq`, `curl`

2. **FileBrowser installieren:**
   ```bash
   wget https://github.com/filebrowser/filebrowser/releases/download/v2.32.0/linux-amd64-filebrowser.tar.gz
   tar -xzf linux-amd64-filebrowser.tar.gz -C /usr/local/bin/ filebrowser
   ```

3. **Benutzer anlegen:**
   ```bash
   systemctl stop filebrowser
   filebrowser config init --config /etc/filebrowser/config.json
   filebrowser users add admin "PASSWORT" --perm.admin --config /etc/filebrowser/config.json
   filebrowser users add pi-sync "PASSWORT" --scope /data \
       --perm.create=false --perm.delete=false --perm.modify=false \
       --perm.rename=false --perm.share=false --perm.download=true --perm.execute \
       --config /etc/filebrowser/config.json
   systemctl start filebrowser
   ```

4. **Nginx + Let's Encrypt:** Config aus `nginx/media-center` anpassen,
   `certbot --nginx` ausführen.

5. **Firewall:** `ufw allow 22/tcp; ufw allow 80/tcp; ufw allow 443/tcp; ufw enable`

## Wartung

- **FileBrowser-Update:** Binary ersetzen, `systemctl restart filebrowser`
- **Zertifikat:** `certbot renew --dry-run` testen, Auto-Renewal via systemd-Timer
- **PHP-Session-GC:** Automatisch, keine manuelle Bereinigung nötig
- **Logs:** `journalctl -u filebrowser`, `/var/log/nginx/media-center.*.log`

## Lizenz

MIT — siehe [LICENSE](LICENSE)

---

*Teil des Media-Center-Projekts · Entwickelt mit KI-Unterstützung*
