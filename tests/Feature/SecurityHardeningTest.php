<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Verifikasi HTTP Security Headers disisipkan pada semua response
     * (Mitigasi Clickjacking, MIME Sniffing, dan XSS).
     */
    public function test_security_headers_are_present_on_all_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    /**
     * Test 2: Verifikasi Rate Limiter memblokir request berlebih pada endpoint publik
     * (Mitigasi DoS dan Brute Force).
     */
    public function test_rate_limiter_blocks_excessive_donation_requests(): void
    {
        $user = User::factory()->create();

        // Limit donasi adalah 10 request per menit
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user)->post('/donation', [
                'amount' => 50000,
                'name' => 'Donatur Test',
            ]);
            // Pastikan tidak kena 429 pada 10 request pertama (bisa 302 redirect atau error validation lain jika midtrans mock)
            $this->assertNotEquals(429, $response->status());
        }

        // Request ke-11 harus diblokir dengan HTTP 429 Too Many Requests
        $excessiveResponse = $this->actingAs($user)->post('/donation', [
            'amount' => 50000,
            'name' => 'Donatur Test',
        ]);

        $excessiveResponse->assertStatus(429);
    }

    /**
     * Test 3: Verifikasi Audit Logging mencatat payload security event ke channel security
     * (Mitigasi Repudiation / Audit Trail).
     */
    public function test_sensitive_actions_trigger_audit_log_record(): void
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('security')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                $decoded = json_decode($message, true);
                return isset($decoded['event']) &&
                    $decoded['event'] === 'DEVSECOPS_SECURITY_AUDIT' &&
                    $decoded['action'] === 'DONATION_CREATE' &&
                    $decoded['details']['amount'] === 75000;
            });

        SecurityAuditService::log('DONATION_CREATE', [
            'donation_id' => 999,
            'order_id' => 'DON-TEST-123',
            'amount' => 75000,
        ], 1);
    }
}
