<?php

namespace App\Membership;

use App\Edulog\Attributes;
use App\Models\Account;
use App\Models\Membership;
use App\Models\Organization;
use EduKSX\Core\Http\ProblemException;
use Illuminate\Support\Facades\DB;

/**
 * Zugehörigkeiten nach I7 und I14: Edulog erzeugt Vorschläge, bestätigt wird durch eine Person mit Rolle `admin` in
 * derselben Organisation (oder die Plattform); erst dann zählen Organisations- und Modulrollen.
 */
final class Memberships
{
    /**
     * Vorschläge aus den Edulog-Institutionen (`o`, Name oder Kürzel). Bestehende Entscheide bleiben unverändert.
     *
     * @return list<Membership>
     */
    public function proposeFromEdulog(Account $account, Attributes $attributes): array
    {
        $out = [];
        foreach ($attributes->values('o') as $institution) {
            $organization = Organization::findByInstitution($institution);
            if ($organization === null) {
                continue;   // unbekannte Institution: kein Vorschlag (Aufnahme der Schule ist ein eigener Ablauf)
            }
            $out[] = Membership::query()->firstOrCreate(
                ['account_id' => $account->id, 'organization_id' => $organization->id],
                ['status' => Membership::PROPOSED, 'source' => 'edulog',
                    'org_role' => $attributes->suggestsAdmin() ? 'admin' : 'member'],
            );
        }

        return $out;
    }

    /**
     * Bestätigen und Modulrollen setzen. `$by` = null: Entscheid der Plattform (Super-Admin, DB-E6).
     *
     * @param  array<string, list<string>>  $moduleRoles  z. B. ['glossary' => ['reader']]
     */
    public function confirm(Membership $membership, ?Account $by, array $moduleRoles = [], ?string $orgRole = null): Membership
    {
        $this->assertMayDecide($membership, $by);

        return DB::transaction(function () use ($membership, $by, $moduleRoles, $orgRole) {
            $membership->update([
                'status' => Membership::CONFIRMED,
                'org_role' => $orgRole ?? $membership->org_role,
                'decided_by' => $by?->id,
                'decided_at' => now(),
            ]);
            $membership->moduleRoles()->delete();
            foreach ($moduleRoles as $module => $roles) {
                foreach (array_unique($roles) as $role) {
                    $membership->moduleRoles()->create(['module' => strtolower($module), 'role' => strtolower($role)]);
                }
            }

            return $membership->refresh();
        });
    }

    public function reject(Membership $membership, ?Account $by): Membership
    {
        $this->assertMayDecide($membership, $by);
        $membership->update(['status' => Membership::REJECTED, 'decided_by' => $by?->id, 'decided_at' => now()]);
        $membership->moduleRoles()->delete();

        return $membership;
    }

    private function assertMayDecide(Membership $membership, ?Account $by): void
    {
        if ($by === null) {
            return;
        }
        if ($by->id === $membership->account_id) {
            throw new ProblemException(403, 'self_confirmation', 'Die eigene Zugehörigkeit kann nicht selbst bestätigt werden.');
        }
        $isAdmin = Membership::query()->where('account_id', $by->id)->where('organization_id', $membership->organization_id)
            ->where('status', Membership::CONFIRMED)->where('org_role', 'admin')->exists();
        if (! $isAdmin) {
            throw new ProblemException(403, 'not_organization_admin', 'Nur Admins der Organisation dürfen bestätigen.');
        }
    }
}
