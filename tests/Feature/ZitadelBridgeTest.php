<?php

namespace Tests\Feature;

use App\Membership\Memberships;
use App\Models\Account;
use App\Models\Membership;
use App\Models\Organization;
use App\Zitadel\SignatureVerifier;
use EduKSX\Core\Http\ProblemException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/** Brücke zu Zitadel (#3–#6): Signatur, Edulog-Abbildung mit Vorschlägen, Bestätigung, Claims, Löschen. */
final class ZitadelBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ident.issuer' => 'https://ident.example',
            'ident.edulog.enabled' => true,
            'ident.signing_keys' => ['claims' => 'k-claims', 'intent' => 'k-intent', 'events' => 'k-events'],
        ]);
    }

    /** @param  array<string, mixed>  $body */
    private function zitadel(string $path, string $target, array|object $body, ?int $time = null): TestResponse
    {
        $json = (string) json_encode($body);

        return $this->call('POST', "/api/v1/zitadel/$path", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ZITADEL_SIGNATURE' => SignatureVerifier::sign($json, "k-$target", $time),
        ], $json);
    }

    /** @param  array<string, list<string>>  $attributes */
    private function intent(array $attributes): array
    {
        return ['response' => [
            'details' => (object) [],
            'idpInformation' => ['rawInformation' => ['attributes' => $attributes]],
            'addHumanUser' => ['profile' => ['preferredLanguage' => 'und'], 'email' => ['sendCode' => (object) []],
                'idpLinks' => [['idpId' => '1', 'userId' => $attributes['EdulogPersonTechID'][0] ?? '']]],
        ]];
    }

    private function edulogPerson(string $role, array $o = ['SMH'], string $tech = '5ca28bef-e599-497f-9ae8-83ae4aef7001'): array
    {
        return ['EdulogPersonTechID' => [$tech], 'uid' => ['l.lehrer@musterhausen.example'], 'givenName' => ['Lara'],
            'sn' => ['Lehrer'], 'mail' => ['l.lehrer@musterhausen.example'], 'EdulogPersonRole' => [$role], 'o' => $o,
            'preferredLanguage' => ['fr-CH']];
    }

    public function test_signature_is_required_and_fresh(): void
    {
        $this->postJson('/api/v1/zitadel/claims', ['user' => ['id' => '1']])->assertStatus(401)
            ->assertJson(['code' => 'invalid_signature']);
        $this->zitadel('claims', 'claims', ['user' => ['id' => '1']], time() - 600)->assertStatus(401);
        $this->zitadel('claims', 'intent', ['user' => ['id' => '1']])->assertStatus(401);   // Schlüssel eines anderen Ziels
        $json = (string) json_encode(['user' => ['id' => '1']]);
        $this->call('POST', '/api/v1/zitadel/claims', [], [], [], ['CONTENT_TYPE' => 'application/json',
            'HTTP_ZITADEL_SIGNATURE' => SignatureVerifier::sign($json, 'falsch')], $json)->assertStatus(401);
    }

    public function test_edulog_teacher_gets_mapped_profile_and_proposal(): void
    {
        $org = Organization::query()->create(['name' => 'Sekundarschule Musterhausen', 'canton' => 'ZH']);
        $org->addAlias('SMH');

        $r = $this->zitadel('edulog-intent', 'intent', $this->intent($this->edulogPerson('teacher##technician', ['SMH', 'Unbekannte Schule'])))->assertOk();

        $r->assertJsonPath('addHumanUser.profile.givenName', 'Lara')
            ->assertJsonPath('addHumanUser.profile.preferredLanguage', 'fr')
            ->assertJsonPath('addHumanUser.email.isVerified', true)
            ->assertJsonPath('addHumanUser.idpLinks.0.userName', 'l.lehrer@musterhausen.example');
        $this->assertStringContainsString('"details":{}', $r->getContent(), 'leere Objekte bleiben {}');
        $account = Account::query()->where('edulog_tech_id', '5ca28bef-e599-497f-9ae8-83ae4aef7001')->firstOrFail();
        $m = $account->memberships()->sole();
        $this->assertSame([Membership::PROPOSED, 'member', 'edulog'], [$m->status, $m->org_role, $m->source]);
    }

    public function test_pupils_and_unknown_roles_get_no_account(): void
    {
        foreach (['pupil', 'legal_guardian', 'other', ''] as $i => $role) {
            $this->zitadel('edulog-intent', 'intent', $this->intent($this->edulogPerson($role, ['SMH'], "tech-$i")))
                ->assertStatus(403)->assertJson(['code' => 'edulog_role_not_allowed']);
        }
        $this->assertSame(0, Account::query()->count());
    }

    public function test_edulog_can_be_disabled_per_environment(): void
    {
        config(['ident.edulog.enabled' => false]);
        $this->zitadel('edulog-intent', 'intent', $this->intent($this->edulogPerson('teacher')))
            ->assertStatus(403)->assertJson(['code' => 'edulog_disabled']);
    }

    public function test_claims_only_after_confirmation(): void
    {
        $org = Organization::query()->create(['name' => 'Sekundarschule Musterhausen']);
        $this->zitadel('edulog-intent', 'intent', $this->intent($this->edulogPerson('principal', ['Sekundarschule Musterhausen'])))->assertOk();
        $meta = [['key' => 'edulog_techid', 'value' => base64_encode('"5ca28bef-e599-497f-9ae8-83ae4aef7001"')]];

        // vor der Bestätigung: angemeldet, aber ohne Organisation und Rollen
        $this->zitadel('claims', 'claims', ['user' => ['id' => 'z-42'], 'user_metadata' => $meta])->assertOk()
            ->assertJsonPath('append_claims.0.value', [])
            ->assertJson(['append_claims' => [1 => ['key' => 'eduksx_roles']]]);
        $account = Account::query()->where('subject', 'z-42')->sole();
        $this->assertSame('5ca28bef-e599-497f-9ae8-83ae4aef7001', $account->edulog_tech_id, 'Edulog-Konto mit Zitadel verknüpft');

        $m = $account->memberships()->sole();
        $this->assertSame('admin', $m->org_role, 'Schulleitung: Vorschlag Organisations-Admin');
        app(Memberships::class)->confirm($m, null, ['glossary' => ['editor', 'reader']]);

        $r = $this->zitadel('claims', 'claims', ['user' => ['id' => 'z-42']])->assertOk();
        $r->assertJsonPath('append_claims.0.value.0', ['id' => $org->id, 'name' => 'Sekundarschule Musterhausen', 'role' => 'admin'])
            ->assertJsonPath('append_claims.1.value', ['glossary' => ['editor', 'reader']]);
    }

    public function test_only_organization_admins_may_confirm(): void
    {
        $org = Organization::query()->create(['name' => 'Schule A']);
        $person = Account::query()->create(['issuer' => 'https://ident.example', 'subject' => 'p']);
        $other = Account::query()->create(['issuer' => 'https://ident.example', 'subject' => 'o']);
        $m = Membership::query()->create(['account_id' => $person->id, 'organization_id' => $org->id,
            'status' => 'proposed', 'org_role' => 'member', 'source' => 'edulog']);

        $this->expectException(ProblemException::class);
        app(Memberships::class)->confirm($m, $other, ['glossary' => ['reader']]);
    }

    public function test_user_removed_deletes_account_and_memberships(): void
    {
        $org = Organization::query()->create(['name' => 'Schule A']);
        $a = Account::query()->create(['issuer' => 'https://ident.example', 'subject' => 'z-9']);
        Membership::query()->create(['account_id' => $a->id, 'organization_id' => $org->id, 'status' => 'confirmed',
            'org_role' => 'member', 'source' => 'manual']);

        $this->zitadel('events', 'events', ['event_type' => 'user.removed', 'aggregateID' => 'z-9'])->assertNoContent();

        $this->assertSame(0, Account::query()->count());
        $this->assertSame(0, Membership::query()->count());
    }
}
