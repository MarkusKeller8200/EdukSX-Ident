# EduKSX-Ident – Projektstand

> Stand 13.11.2026 (23:30): Brücke 0.1 im Pull Request (#2–#6); gegen das PoC-Zitadel (v4.20.3, Login V2, Edulog-Ersatz)
> von Anfang bis Ende geprüft. Als Nächstes: S4-Ereignisse an die Module, Portal für Organisations-Admins.

## Umgesetzt

| Bereich | Stand | Seit Version | Konzept |
|---|---|---|---|
| Grundgerüst mit eduksx/core | im PR | 0.1.0 | EduKSX-Architektur KONZEPT-IDENT |
| Organisationen, Konten, Zugehörigkeiten | im PR | 0.1.0 | KONZEPT-IDENT 2.1, I7, I14 |
| Claims, Edulog-Abbildung, Ereignisse | im PR | 0.1.0 | KONZEPT-IDENT 2.2, P4–P6 |

## Offen bzw. geplant

- S4: `ident.user.deleted` u. a. als CloudEvent an die Module (API-Standard B7)
- Portal für Organisations-Admins (Bestätigen, Rollen, Einladungen); bis dahin `php artisan ident:confirm`
- Historie (`data_changes`) mit Core 0.2

## Technik

- Laravel 13, PHP 8.4, `eduksx/core`; Routen in `routes/api.php` (`/api/v1/zitadel/*`, signiert).
- Prüfen: `composer ci:check`; PostgreSQL wie im README.
