<?php

namespace App\Edulog;

/**
 * Edulog-Attribute nach dem Leitfaden für Identitätsanbieter 1.2.1: Namen mit genauer Schreibweise, Mehrfachwerte als
 * mehrere Werte oder mit `##` getrennt (z. B. Entra ID). Liest beide Formen.
 */
final class Attributes
{
    /** @param  array<string, mixed>  $raw */
    public function __construct(private readonly array $raw) {}

    /** @return list<string> */
    public function values(string $name): array
    {
        $value = $this->raw[$name] ?? [];
        $out = [];
        foreach (is_array($value) ? $value : [$value] as $item) {
            foreach (explode('##', (string) $item) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $out[] = $part;
                }
            }
        }

        return array_values(array_unique($out));
    }

    public function first(string $name): string
    {
        return $this->values($name)[0] ?? '';
    }

    public function techId(): string
    {
        return $this->first('EdulogPersonTechID');
    }

    /** @return list<string> */
    public function roles(): array
    {
        return $this->values('EdulogPersonRole');
    }

    /** Hat die Person eine Rolle mit Zugang? (Lernende, Erziehungsberechtigte, other, leer: nein) */
    public function hasAllowedRole(): bool
    {
        return array_intersect($this->roles(), (array) config('ident.edulog.allowed_roles')) !== [];
    }

    public function suggestsAdmin(): bool
    {
        return array_intersect($this->roles(), (array) config('ident.edulog.admin_roles')) !== [];
    }

    /** Sprache für das Profil: zwei Buchstaben, Vorgabe de. */
    public function language(): string
    {
        return substr($this->first('preferredLanguage') ?: 'de', 0, 2);
    }
}
