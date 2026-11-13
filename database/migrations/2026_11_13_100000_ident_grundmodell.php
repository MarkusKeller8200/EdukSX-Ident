<?php

use EduKSX\Core\Database\StrictSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grundmodell der Ident-Brücke (#3, KONZEPT-IDENT 2.1, I7, I14): Organisationen mit Kürzeln, Konten, Mitgliedschaften
 * mit Bestätigung und Modulrollen. STRICT mit CHECK-Regeln (DB-Standard A4), UUID v7 als Schlüssel (DB-E3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name', 200);
            $t->string('canton', 2)->nullable();
            $t->unsignedInteger('lock_version')->default(0);
            $t->timestamps();
        });
        StrictSchema::harden('organizations', [
            'organizations_name_check' => 'length("name") between 1 and 200',
            'organizations_canton_check' => '"canton" is null or length("canton") = 2',
        ]);

        Schema::create('organization_aliases', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $t->string('alias', 100);
            $t->string('alias_normalized', 100);
            $t->timestamps();
            $t->unique(['organization_id', 'alias_normalized']);
            $t->index('alias_normalized');
        });
        StrictSchema::harden('organization_aliases', [
            'organization_aliases_alias_check' => 'length("alias") between 1 and 100',
        ]);

        Schema::create('accounts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('issuer', 255);
            $t->string('subject', 255)->nullable();
            $t->string('edulog_tech_id', 36)->nullable()->unique();
            $t->string('display_name', 200)->nullable();
            $t->string('email', 254)->nullable();
            $t->unsignedInteger('lock_version')->default(0);
            $t->timestamps();
            $t->unique(['issuer', 'subject']);
        });
        StrictSchema::harden('accounts', [
            'accounts_identity_check' => '"subject" is not null or "edulog_tech_id" is not null',
        ]);

        Schema::create('memberships', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $t->string('status', 20);
            $t->string('org_role', 20);
            $t->string('source', 20);
            $t->foreignUuid('decided_by')->nullable()->constrained('accounts')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->unsignedInteger('lock_version')->default(0);
            $t->timestamps();
            $t->unique(['account_id', 'organization_id']);
            $t->index('organization_id');
            $t->index('decided_by');
        });
        StrictSchema::harden('memberships', [
            'memberships_status_check' => "\"status\" in ('proposed', 'confirmed', 'rejected')",
            'memberships_org_role_check' => "\"org_role\" in ('admin', 'member')",
            'memberships_source_check' => "\"source\" in ('edulog', 'invitation', 'manual')",
        ]);

        Schema::create('module_roles', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('membership_id')->constrained()->cascadeOnDelete();
            $t->string('module', 20);
            $t->string('role', 50);
            $t->timestamps();
            $t->unique(['membership_id', 'module', 'role']);
        });
        StrictSchema::harden('module_roles', [
            'module_roles_module_check' => 'length("module") between 2 and 20 and "module" = lower("module")',
            'module_roles_role_check' => 'length("role") between 1 and 50 and "role" = lower("role")',
        ]);
    }

    public function down(): void
    {
        foreach (['module_roles', 'memberships', 'accounts', 'organization_aliases', 'organizations'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
