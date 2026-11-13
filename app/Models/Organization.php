<?php

namespace App\Models;

use EduKSX\Core\Eloquent\OptimisticLocking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Organisation (Schule, Schulgemeinde, Gemeinde). `id` ist die Plattform-ID (DB-Standard E3).
 *
 * @property string $id
 * @property string $name
 * @property ?string $canton
 */
class Organization extends Model
{
    use HasUuids, OptimisticLocking;

    protected $guarded = ['id', 'lock_version'];

    /** @return HasMany<OrganizationAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(OrganizationAlias::class);
    }

    public function addAlias(string $alias): OrganizationAlias
    {
        return $this->aliases()->firstOrCreate(['alias_normalized' => self::normalize($alias)], ['alias' => $alias]);
    }

    public static function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));
    }

    /** Organisation zu einer Edulog-Institution (`o`): über den Namen oder ein Kürzel. */
    public static function findByInstitution(string $institution): ?self
    {
        $key = self::normalize($institution);
        if ($key === '') {
            return null;
        }

        return self::query()->whereRaw('lower(name) = ?', [$key])->first()
            ?? self::query()->whereHas('aliases', fn ($q) => $q->where('alias_normalized', $key))->first();
    }
}
