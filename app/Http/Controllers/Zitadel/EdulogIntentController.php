<?php

namespace App\Http\Controllers\Zitadel;

use App\Edulog\Attributes;
use App\Membership\Memberships;
use App\Models\Account;
use EduKSX\Core\Http\ProblemException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Actions v2 Response auf `RetrieveIdentityProviderIntent` (#5, Login V2): Edulog-Attribute abbilden, Lernende und
 * unbekannte Rolle abweisen, Konto und Vorschläge für Zugehörigkeiten anlegen. Die Antwort wird als Objekt
 * weitergereicht (leere Objekte bleiben `{}` – sonst lehnt Zitadel das Protobuf-JSON ab).
 */
final class EdulogIntentController
{
    public function __invoke(Request $request, Memberships $memberships): JsonResponse
    {
        $payload = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $response = $payload->response ?? new \stdClass;
        $raw = json_decode(json_encode($response->idpInformation->rawInformation ?? new \stdClass), true);
        $raw = $raw['attributes'] ?? $raw;

        if (! array_key_exists('EdulogPersonTechID', (array) $raw)) {
            return response()->json($response);   // kein Edulog: unverändert durchreichen
        }
        $a = new Attributes((array) $raw);
        if (! config('ident.edulog.enabled')) {
            throw new ProblemException(403, 'edulog_disabled', 'Anmeldung über Edulog ist in dieser Umgebung noch nicht freigeschaltet.');
        }
        if (! $a->hasAllowedRole()) {
            throw new ProblemException(403, 'edulog_role_not_allowed', 'Für diese Rolle ist EduKSX noch nicht freigegeben.');
        }

        $account = Account::query()->firstOrCreate(['edulog_tech_id' => $a->techId()], [
            'issuer' => (string) config('ident.issuer'),
            'display_name' => trim($a->first('givenName').' '.$a->first('sn')),
            'email' => $a->first('mail') ?: null,
        ]);
        $memberships->proposeFromEdulog($account, $a);

        $profile = (object) ['givenName' => $a->first('givenName'), 'familyName' => $a->first('sn'),
            'displayName' => trim($a->first('givenName').' '.$a->first('sn')), 'preferredLanguage' => $a->language()];
        $email = (object) ['email' => $a->first('mail'), 'isVerified' => true];
        $metadata = [(object) ['key' => 'edulog_techid', 'value' => base64_encode((string) json_encode($a->techId()))]];
        $linkName = $a->first('uid') ?: $a->first('mail');

        foreach (['addHumanUser' => $response->addHumanUser ?? null, 'human' => $response->createUser->human ?? null] as $user) {
            if ($user === null) {
                continue;
            }
            foreach ($user->idpLinks ?? [] as $link) {
                $link->userName = $linkName;
            }
            $user->profile = $profile;
            $user->email = $email;
            $user->metadata = $metadata;
        }
        if (isset($response->addHumanUser)) {
            $response->addHumanUser->username = $a->first('mail');
        }
        if (isset($response->createUser)) {
            $response->createUser->username = $a->first('mail');
        }

        return response()->json($response);
    }
}
