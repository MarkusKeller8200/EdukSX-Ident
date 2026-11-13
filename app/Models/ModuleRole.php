<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Rolle in einem Modul (Schlüssel aus dem Modulregister), gilt nur für bestätigte Mitgliedschaften. */
class ModuleRole extends Model
{
    use HasUuids;

    protected $guarded = ['id'];
}
