# Media Center — Backend Server

Server-seitige Infrastruktur für das [Media Center](https://b481.de/media-center/).

**Server:** Strato VPS (Debian 13) · **Domain:** media.b481.de

## Architektur

```
nginx (443) → FileBrowser (127.0.0.1:8080)
            → PHP 8.4 Admin-Panel (/admin, /demo)
```

## Verzeichnisse

| Pfad | Inhalt |
|---|---|
| `filebrowser/` | FileBrowser-Konfiguration (v2.32.0, single binary, SQLite-DB) |
| `nginx/` | Reverse-Proxy Config mit Let's Encrypt, PHP-FPM |
| `systemd/` | filebrowser.service (auto-restart) |
| `www/admin/` | PHP-Admin-Panel (Radio/YouTube-Editor, WiFi-Remote) |
| `www/demo/` | Isolierte Demo-Instanz (demo/demo2026) |
| `data/` | radio_stations.json, youtube_channels.json (Live-Daten) |

## Dienste

- **FileBrowser** — Web-Dateimanager für Hörbuch-Uploads und JSON-Konfiguration
- **Admin-Panel** — Radio-Stationen und YouTube-Kanäle live editieren
- **WiFi-Agent** — Poll-basierte Fernsteuerung des Raspberry Pi (Netzwerkwechsel)
- **Pull-Sync** — Pi holt alle 10 Min Konfiguration + Hörbücher per Cron

## Setup

Siehe [strato-vps-filebrowser-setup](../../tree/main/strato-vps-filebrowser-setup) Skill für die vollständige Installationsanleitung.

## Lizenz

MIT — siehe [LICENSE](LICENSE)
