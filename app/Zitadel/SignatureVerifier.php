<?php

namespace App\Zitadel;

use Illuminate\Http\Request;

/**
 * Prüft die Kopfzeile `ZITADEL-Signature: t=<Unixzeit>,v1=<HMAC-SHA256(Schlüssel, "t.Body")>` von Actions v2.
 * Zu alte oder künftige Zeitstempel (> 5 Minuten) werden abgewiesen (API-Standard B7, PoC P6).
 */
final class SignatureVerifier
{
    public function __construct(private readonly int $maxAge = 300) {}

    public function verify(Request $request, ?string $key): bool
    {
        if ($key === null || $key === '') {
            return false;
        }
        $parts = [];
        foreach (explode(',', (string) $request->header('ZITADEL-Signature', '')) as $part) {
            [$name, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            $parts[$name] = $value;
        }
        if (! isset($parts['t'], $parts['v1']) || ! ctype_digit($parts['t'])) {
            return false;
        }
        if (abs(time() - (int) $parts['t']) > $this->maxAge) {
            return false;
        }
        $expected = hash_hmac('sha256', $parts['t'].'.'.$request->getContent(), $key);

        return hash_equals($expected, $parts['v1']);
    }

    /** Für Tests und Werkzeuge: gültige Kopfzeile erzeugen. */
    public static function sign(string $body, string $key, ?int $time = null): string
    {
        $t = $time ?? time();

        return 't='.$t.',v1='.hash_hmac('sha256', $t.'.'.$body, $key);
    }
}
