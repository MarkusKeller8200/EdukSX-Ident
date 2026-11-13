# EduKSX-Ident

Ident-Brücke der EduKSX-Plattform (Modul ident): verbindet Zitadel mit den Modulen – Plattform-Claims eduksx_orgs/eduksx_roles, Edulog-Abbildung, Schulzugehörigkeit mit Bestätigung, Ereignisse an die Module – für Schulen, Schulgemeinden und Gemeinden.

## Aufgabe

EduKSX-Ident ist die **Brücke** zwischen Zitadel (Anmeldung) und den Modulen der Plattform (KONZEPT-IDENT im Repo
EduKSX-Architektur, I4 und I14). Zitadel meldet an; Ident entscheidet, zu welchen Organisationen eine Person gehört und
welche Rollen sie in den Modulen hat:

| Aufruf aus Zitadel (Actions v2, signiert) | Pfad | Zweck |
| --- | --- | --- |
| `preuserinfo`, `preaccesstoken` | `POST /api/v1/zitadel/claims` | Claims `eduksx_orgs` und `eduksx_roles` – nur bestätigte Zugehörigkeiten |
| Antwort `RetrieveIdentityProviderIntent` (Login V2) | `POST /api/v1/zitadel/edulog-intent` | Edulog-Attribute abbilden, Lernende und unbekannte Rolle abweisen, Vorschläge anlegen |
| Ereignisse `user.removed` u. a. | `POST /api/v1/zitadel/events` | Konto und Zugehörigkeiten löschen |

Aus Edulog entsteht nur ein **Vorschlag**; erst ein Organisations-Admin (bzw. bis zum Portal die Plattform mit
`php artisan ident:confirm <id> --role=glossary:reader`) bestätigt. Organisationen mit Kürzeln:
`php artisan ident:organization "Sekundarschule Musterhausen" --alias=SMH --canton=ZH`.

Edulog wird je Umgebung geschaltet (`IDENT_EDULOG_ENABLED`): auf dev an, produktiv erst nach dem Go.

## Entwicklung

PHP 8.4, Composer. `composer install`, `.env` aus `.env.example`, `php artisan key:generate`, `php artisan migrate --seed`.
Prüfen: `composer ci:check`; PostgreSQL: `DB_CONNECTION=pgsql DB_DATABASE=… composer test`.

## Mitmachen

Fehler, Verbesserungen und Ideen bitte über die Issue-Formulare melden. Wie Meldungen freigegeben und umgesetzt
werden, steht in [CONTRIBUTING.md](CONTRIBUTING.md) und [docs/GITHUB-ABLAUF.md](docs/GITHUB-ABLAUF.md).
Sicherheitslücken bitte nicht öffentlich melden, sondern wie in [SECURITY.md](SECURITY.md) beschrieben.

## Versionen

Änderungen stehen im [CHANGELOG.md](CHANGELOG.md). Releases entstehen nach Freigabe automatisch (`VERSION`,
Workflow `release.yml`).
