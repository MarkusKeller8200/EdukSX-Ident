<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;

/** Organisation mit Kürzeln anlegen: `php artisan ident:organization "Sekundarschule Musterhausen" --alias=SMH --canton=ZH`. */
final class AddOrganization extends Command
{
    protected $signature = 'ident:organization {name} {--alias=*} {--canton=}';

    protected $description = 'Organisation (Schule, Schulgemeinde, Gemeinde) mit Namen und Kürzeln anlegen';

    public function handle(): int
    {
        $org = Organization::query()->firstOrCreate(['name' => $this->argument('name')], ['canton' => $this->option('canton') ?: null]);
        foreach ((array) $this->option('alias') as $alias) {
            $org->addAlias((string) $alias);
        }
        $this->line($org->id);

        return self::SUCCESS;
    }
}
