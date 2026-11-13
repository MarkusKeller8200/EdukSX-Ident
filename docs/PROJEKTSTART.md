# Projektstart – EduKSX-Ident

Stand: 2026-11-13 · Status: **OFFEN** – Grundgerüst erst nach Bestätigung durch Projektverantwortliche:r

Steckbrief aus der Start-Checkliste (`start.py --ziel …` im GitHub-Blueprint). Er ist die Grundlage für das Grundgerüst
und für die Konfiguration in `.github/blueprint.toml`. Änderungen hier festhalten und `start.py` erneut ausführen.

## Ziel

Ident-Brücke der EduKSX-Plattform (Modul ident): verbindet Zitadel mit den Modulen – Plattform-Claims eduksx_orgs/eduksx_roles, Edulog-Abbildung, Schulzugehörigkeit mit Bestätigung, Ereignisse an die Module – für Schulen, Schulgemeinden und Gemeinden.

**Nichtziele:**

- keine eigene Anmeldung und keine Passwörter (macht Zitadel)
- keine Inhalte der Module
- kein Identitätsanbieter gegenüber Edulog (I12)

## Datenschutz

- Zielgruppe: öffentliche Organe
- Anwendbares Recht: IDG (Kanton Zürich)
- Daten: Personendaten · Schutzbedarf: **vertraulich**

Die Zuordnung ist eine Arbeitshilfe, keine Rechtsberatung; bei öffentlichen Organen die zuständige
Datenschutzstelle einbeziehen (z. B. Vorabkontrolle bzw. Datenschutz-Folgenabschätzung).

## Antworten

| Frage | Antwort |
|---|---|
| Wie heisst das Projekt (voller Name)? | EduKSX-Ident |
| Was soll das Projekt leisten, und für wen? (ein bis zwei Sätze) | Ident-Brücke der EduKSX-Plattform (Modul ident): verbindet Zitadel mit den Modulen – Plattform-Claims eduksx_orgs/eduksx_roles, Edulog-Abbildung, Schulzugehörigkeit mit Bestätigung, Ereignisse an die Module – für Schulen, Schulgemeinden und Gemeinden. |
| Was gehört ausdrücklich nicht dazu (Nichtziele)? | keine eigene Anmeldung und keine Passwörter (macht Zitadel), keine Inhalte der Module, kein Identitätsanbieter gegenüber Edulog (I12) |
| Welcher Kurzname soll in den Release-Titeln stehen? | EduKSX-Ident |
| Ist es eine Web-Anwendung, eine Desktop-Anwendung oder beides? | web |
| Wer nutzt die Anwendung? | öffentliche Organe |
| Welche öffentlichen Organe? | Kanton Zürich |
| Ist das Repository neu oder gibt es es schon? | neu |
| Wer gibt Issues und Releases frei (Rolle genügt)? | Projektverantwortliche:r |
| Arbeitest du allein oder im Team? | allein |
| Ist das Repository öffentlich oder privat? | öffentlich |
| Unter welcher Lizenz steht der Code? | AGPL-3.0-or-later |
| Auf wen lautet die Lizenz? | Markus Keller |
| In welchen Programmiersprachen wird entwickelt? | PHP |
| Welcher Grundsatz gilt für Abhängigkeiten? | geprüfte Pakete |
| Wo läuft die Web-Anwendung? | eigener Server / VPS |
| Wie kommt die Anwendung zu den Nutzer:innen? | Server |
| Welches Framework bzw. welche Grundlage? | Laravel 13 (PHP 8.4) mit eduksx/core |
| Welche Daten verarbeitet die Anwendung? | Personendaten |
| Welcher Schutzbedarf gilt für die Daten (Klassifizierung)? | vertraulich |
| Welche Anmeldungen und Zugänge braucht es? | externe Dienste (API) |
| In welchen Sprachen wird die Oberfläche ausgeliefert? | de-CH |
| Gibt es Plugins oder Module mit eigener Version? | nein |
| Mit welchem Befehl laufen alle Prüfungen? | composer ci:check |
| In welcher Datei steht die Versionsnummer? | VERSION |
| Mit welcher Version startet das Projekt? | 0.1.0 |
| Mit welchen KI-Agenten wird gearbeitet? | Claude |
| Wie heisst der erste Meilenstein? | 0.1.0 – Brücke |
| Übergabe und Projektstand auch als Kopie im Claude-Projekt führen? | nein |
| Welche ersten Funktionen gehören in diesen Meilenstein? | Grundgerüst Laravel mit eduksx/core, Datenmodell: Organisationen mit Kürzeln, Konten, Mitgliedschaften mit Bestätigung, Claims eduksx_orgs und eduksx_roles für Zitadel (Actions v2), Edulog-Abbildung für Login V2 (Actions v2), Ereignisse aus Zitadel empfangen und prüfen |

## Grundgerüst

Vorschlag für Aufbau, Ordner und erste Dateien – wird vor dem Anlegen mit Projektverantwortliche:r
abgestimmt (Status je Punkt: BESCHLOSSEN, EMPFOHLEN, OFFEN, ZU PRÜFEN):

| Punkt | Vorschlag | Status |
|---|---|---|
| Technik | PHP · Laravel 13 (PHP 8.4) mit eduksx/core | OFFEN |
| Ordner | Quellcode, `tests/`, `docs/` | OFFEN |
| Prüfungen | `composer ci:check` (Workflow «pruefungen») | OFFEN |
| Version | `VERSION` mit 0.1.0 | OFFEN |
| Auslieferung | Server | OFFEN |

## Erster Meilenstein: 0.1.0 – Brücke

- [ ] Grundgerüst Laravel mit eduksx/core
- [ ] Datenmodell: Organisationen mit Kürzeln, Konten, Mitgliedschaften mit Bestätigung
- [ ] Claims eduksx_orgs und eduksx_roles für Zitadel (Actions v2)
- [ ] Edulog-Abbildung für Login V2 (Actions v2)
- [ ] Ereignisse aus Zitadel empfangen und prüfen

Diese Punkte werden nach Bestätigung als Issues mit Testliste angelegt.
