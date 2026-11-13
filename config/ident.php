<?php

return [
    /*
    | Aussteller der Tokens (Zitadel); zusammen mit `sub` der Schlüssel eines Kontos (DB-Standard B4).
    */
    'issuer' => env('IDENT_ISSUER', 'http://localhost:8090'),

    /*
    | Signaturschlüssel der Actions-v2-Ziele in Zitadel (je Ziel ein eigener Schlüssel, nie im Repository).
    */
    'signing_keys' => [
        'claims' => env('IDENT_ZITADEL_CLAIMS_KEY'),
        'intent' => env('IDENT_ZITADEL_INTENT_KEY'),
        'events' => env('IDENT_ZITADEL_EVENTS_KEY'),
    ],

    /*
    | Höchstalter einer signierten Anfrage aus Zitadel in Sekunden (API-Standard B7: 5 Minuten).
    */
    'signature_max_age' => 300,

    /*
    | Edulog (KONZEPT-IDENT 2.1): Rollen mit Zugang; alle anderen (pupil, legal_guardian, other, leer) erhalten kein
    | Konto. Rollen, die eine Organisations-Admin-Rolle vorschlagen (Bestätigung nötig, I7).
    */
    'edulog' => [
        'enabled' => (bool) env('IDENT_EDULOG_ENABLED', false),
        'allowed_roles' => ['teacher', 'administration', 'principal', 'technician'],
        'admin_roles' => ['principal', 'administration'],
    ],
];
