<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemHealthTest extends TestCase
{
    public function test_health_ok(): void
    {
        $this->get('/api/health')
            ->assertOk()
            ->assertJson(['database' => 'ok']);
    }

    public function test_health_returns_503_when_database_down(): void
    {
        config(['database.connections.sqlite.database' => '/x/tidak-ada.sqlite']);
        DB::purge();

        $this->get('/api/health')->assertStatus(503);
    }

    public function test_response_has_request_id_header(): void
    {
        $this->get('/api/health')->assertHeader('X-Request-ID');
    }

    public function test_request_id_is_reused_from_request(): void
    {
        $this->withHeader('X-Request-ID', 'abc')
            ->get('/api/health')
            ->assertHeader('X-Request-ID', 'abc');
    }

    public function test_check_config_fails_when_key_missing(): void
    {
        config(['midtrans.server_key' => null]);

        $this->artisan('retide:check-config')->assertFailed();
    }

    public function test_check_config_never_prints_secret(): void
    {
        config(['midtrans.server_key' => 'rahasia123']);

        $this->artisan('retide:check-config')
            ->doesntExpectOutputToContain('rahasia123');
    }
}
