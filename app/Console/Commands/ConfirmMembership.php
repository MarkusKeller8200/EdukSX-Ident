<?php

namespace App\Console\Commands;

use App\Membership\Memberships;
use App\Models\Membership;
use Illuminate\Console\Command;

/**
 * Plattform-Entscheid über eine Zugehörigkeit (bis zum Portal, #3): `php artisan ident:confirm <id> --role=glossary:reader`.
 */
final class ConfirmMembership extends Command
{
    protected $signature = 'ident:confirm {membership} {--role=* : modul:rolle} {--admin : als Organisations-Admin} {--reject}';

    protected $description = 'Zugehörigkeit bestätigen (mit Modulrollen) oder ablehnen – Entscheid der Plattform';

    public function handle(Memberships $memberships): int
    {
        $m = Membership::query()->findOrFail($this->argument('membership'));
        if ($this->option('reject')) {
            $memberships->reject($m, null);
            $this->info('Abgelehnt.');

            return self::SUCCESS;
        }
        $roles = [];
        foreach ((array) $this->option('role') as $spec) {
            [$module, $role] = array_pad(explode(':', (string) $spec, 2), 2, '');
            if ($module === '' || $role === '') {
                $this->error("Rolle «{$spec}» muss die Form modul:rolle haben.");

                return self::INVALID;
            }
            $roles[$module][] = $role;
        }
        $memberships->confirm($m, null, $roles, $this->option('admin') ? 'admin' : null);
        $this->info('Bestätigt.');

        return self::SUCCESS;
    }
}
