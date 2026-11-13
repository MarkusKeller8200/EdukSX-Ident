<?php

namespace App\Models;

use EduKSX\Core\Eloquent\OptimisticLocking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Konto einer Person: eindeutig über `issuer` + `subject` (DB-Standard B4); bei Edulog zusätzlich über die
 * `EdulogPersonTechID`, solange Zitadel das Konto noch nicht kennt.
 *
 * @property string $id
 * @property string $issuer
 * @property ?string $subject
 * @property ?string $edulog_tech_id
 */
class Account extends Model
{
    use HasUuids, OptimisticLocking;

    protected $guarded = ['id', 'lock_version'];

    /** @return HasMany<Membership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}
