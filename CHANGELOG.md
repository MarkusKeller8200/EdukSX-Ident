# Changelog – EduKSX-Ident

Format angelehnt an [Keep a Changelog](https://keepachangelog.com/de/), Versionen nach
[Semantic Versioning](https://semver.org/lang/de/) (MAJOR.MINOR.PATCH). Neue Einträge kommen laufend unter
**Unveröffentlicht**; `python3 .github/blueprint/release.py set X.Y.Z --commit` macht daraus eine Version.

## [Unveröffentlicht]

### Neu

- **Grundgerüst (#2):** Laravel 13 mit `eduksx/core` (Modulschlüssel `ident`, Fehler als problem+json, Konformitätstest).
- **Datenmodell (#3):** Organisationen mit Kürzeln, Konten (`issuer` + `subject`, Edulog-Kennung), Zugehörigkeiten mit Vorschlag und Bestätigung, Modulrollen; Befehle `ident:organization` und `ident:confirm`.
- **Claims für Zitadel (#4):** `eduksx_orgs` und `eduksx_roles` nur aus bestätigten Zugehörigkeiten; ohne Bestätigung leer.
- **Edulog für Login V2 (#5):** Abbildung der Attribute (Mehrfachwerte mit `##`), kein Konto für Lernende und unbekannte Rolle, Vorschläge über Name oder Kürzel der Institution; je Umgebung schaltbar.
- **Ereignisse aus Zitadel (#6):** Signatur und Zeitstempel geprüft; `user.removed` löscht Konto und Zugehörigkeiten.
