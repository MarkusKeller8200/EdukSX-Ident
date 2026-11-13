<?php

namespace Tests\Feature;

use EduKSX\Core\Testing\AssertsConformance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Konformitätstest der Plattform aus eduksx/core (DB-Standard E5, API-Standard D8). */
final class ConformanceTest extends TestCase
{
    use AssertsConformance, RefreshDatabase;

    public function test_datenbank(): void
    {
        $this->assertDatabaseConforms();
    }

    public function test_api_grundlagen(): void
    {
        $this->assertApiBasicsConform();
    }
}
