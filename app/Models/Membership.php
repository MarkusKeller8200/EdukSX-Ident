<?php

namespace App\Models;

use EduKSX\Core\Eloquent\OptimisticLocking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zugehörigkeit eines Kontos zu einer Organisation (I14). Aus Edulog entsteht nur ein Vorschlag (`proposed`); erst ein
 * Organisations-Admin bestätigt (`confirmed`) und vergibt Modulrollen (I7).
 *
 * @property string $status
 * @property string $org_role
 * @property string $source
 */
class Membership extends Model
{
    use HasUuids, OptimisticLocking;

    public const PROPOSED = 'proposed';

    public const CONFIRMED = 'confirmed';

    public const REJECTED = 'rejected';

    protected $guarded = ['id', 'lock_version'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<ModuleRole, $this> */
    public function moduleRoles(): HasMany
    {
        return $this->hasMany(ModuleRole::class);
    }
}
