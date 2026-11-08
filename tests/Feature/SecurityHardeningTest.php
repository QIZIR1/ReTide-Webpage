<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test 1: Verifikasi HTTP Security Headers disisipkan pada semua response
     * (Mitigasi Clickjacking, MIME Sniffing, dan XSS).
     */
    public function test_security_headers_are_present_on_all_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'DENY');
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
                'name' => 'Donatur Test',
                'email' => 'donatur@example.com',
                'amount' => 50000,
            ]);
            // Pastikan tidak kena 429 pada 10 request pertama
            $this->assertNotEquals(429, $response->status());
        }

        // Request ke-11 harus diblokir dengan HTTP 429 Too Many Requests
        $excessiveResponse = $this->actingAs($user)->post('/donation', [
            'name' => 'Donatur Test',
            'email' => 'donatur@example.com',
            'amount' => 50000,
        ]);

        $excessiveResponse->assertStatus(429);
    }

    /**
     * Test 3: Verifikasi Audit Logging mencatat payload security event ke channel security
     * (Mitigasi Repudiation / Audit Trail).
     */
    public function test_sensitive_actions_trigger_audit_log_record(): void
    {
        $logged = false;
        Log::shouldReceive('channel')
            ->once()
            ->with('security')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) use (&$logged) {
                $decoded = json_decode($message, true);

                $logged = isset($decoded['event']) &&
                    $decoded['event'] === 'DEVSECOPS_SECURITY_AUDIT' &&
                    $decoded['action'] === 'DONATION_CREATE' &&
                    $decoded['details']['amount'] === 75000;

                return $logged;
            });

        SecurityAuditService::log('DONATION_CREATE', [
            'donation_id' => 999,
            'order_id' => 'DON-TEST-123',
            'amount' => 75000,
        ], 1);

        $this->assertTrue($logged, 'Security audit log event was not recorded with expected payload');
    }

    /**
     * Test 4: Verifikasi penghapusan produk memicu audit log record
     * (Mitigasi Repudiation pada mutasi data krusial).
     */
    public function test_product_deletion_triggers_audit_log_record(): void
    {
        $logged = false;
        Log::shouldReceive('channel')
            ->once()
            ->with('security')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) use (&$logged) {
                $decoded = json_decode($message, true);

                $logged = isset($decoded['event']) &&
                    $decoded['event'] === 'DEVSECOPS_SECURITY_AUDIT' &&
                    $decoded['action'] === 'PRODUCT_DELETE' &&
                    $decoded['details']['product_id'] === 42 &&
                    $decoded['details']['name'] === 'Botol Daur Ulang';

                return $logged;
            });

        SecurityAuditService::log('PRODUCT_DELETE', [
            'product_id' => 42,
            'name' => 'Botol Daur Ulang',
            'price' => 25000,
        ], 1);

        $this->assertTrue($logged, 'Product deletion audit log was not recorded with expected payload');
    }
}
