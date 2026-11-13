<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Name oder Kürzel, unter dem eine Organisation in Edulog (`o`) erscheint. */
class OrganizationAlias extends Model
{
    use HasUuids;

    protected $guarded = ['id'];
}
