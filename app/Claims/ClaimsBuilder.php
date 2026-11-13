<?php

namespace App\Claims;

use App\Models\Account;
use App\Models\Membership;

/**
 * Plattform-Claims nach spec/v1/oidc-claims.schema.json: nur bestätigte Mitgliedschaften; Modulrollen je Modulschlüssel.
 * Ohne Bestätigung: `eduksx_orgs` leer, `eduksx_roles` leeres Objekt – das Modul zeigt «Zugang wartet auf Bestätigung».
 */
final class ClaimsBuilder
{
    /** @return array{eduksx_orgs: list<array{id: string, name: string, role: string}>, eduksx_roles: object} */
    public function forAccount(Account $account): array
    {
        $memberships = $account->memberships()->where('status', Membership::CONFIRMED)
            ->with(['organization', 'moduleRoles'])->get();
        $orgs = [];
        $roles = [];
        foreach ($memberships as $m) {
            $orgs[] = ['id' => $m->organization->id, 'name' => $m->organization->name, 'role' => $m->org_role];
            foreach ($m->moduleRoles as $r) {
                $roles[$r->module][] = $r->role;
            }
        }
        $roles = array_map(fn (array $r) => array_values(array_unique($r)), $roles);
        ksort($roles);

        return ['eduksx_orgs' => $orgs, 'eduksx_roles' => (object) $roles];
    }
}
