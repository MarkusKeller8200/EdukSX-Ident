<?php

namespace App\Http\Controllers\Zitadel;

use App\Claims\ClaimsBuilder;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Actions v2 `preuserinfo` / `preaccesstoken` (#4): ergänzt die Claims eduksx_orgs und eduksx_roles. Verknüpft dabei
 * ein Edulog-Konto mit seiner Zitadel-Kennung (`sub`), sobald Zitadel sie kennt.
 */
final class ClaimsController
{
    public function __invoke(Request $request, ClaimsBuilder $claims): JsonResponse
    {
        $subject = (string) $request->input('user.id', $request->input('userinfo.sub', ''));
        $issuer = (string) config('ident.issuer');
        $techId = null;
        foreach ((array) $request->input('user_metadata', []) as $meta) {
            if (($meta['key'] ?? '') === 'edulog_techid') {
                $techId = json_decode((string) base64_decode((string) ($meta['value'] ?? ''), true), true);
            }
        }

        $account = Account::query()->where('issuer', $issuer)->where('subject', $subject)->first();
        if ($account === null && is_string($techId) && $techId !== '') {
            $account = Account::query()->where('edulog_tech_id', $techId)->first();
            $account?->update(['subject' => $subject, 'issuer' => $issuer]);
        }
        $account ??= Account::query()->create(['issuer' => $issuer, 'subject' => $subject,
            'display_name' => $request->input('userinfo.name'), 'email' => $request->input('userinfo.email')]);

        $c = $claims->forAccount($account);

        return response()->json(['append_claims' => [
            ['key' => 'eduksx_orgs', 'value' => $c['eduksx_orgs']],
            ['key' => 'eduksx_roles', 'value' => $c['eduksx_roles']],
        ]]);
    }
}
