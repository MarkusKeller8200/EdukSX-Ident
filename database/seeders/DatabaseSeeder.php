<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

/** Erfundene Testdaten für dev (PoC #36): zwei Organisationen mit Kürzeln. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->firstOrCreate(['name' => 'Sekundarschule Musterhausen'], ['canton' => 'ZH'])->addAlias('SMH');
        Organization::query()->firstOrCreate(['name' => 'Schulgemeinde Beispielthal'], ['canton' => 'ZH']);
    }
}
