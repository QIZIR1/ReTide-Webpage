<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SecurityAuditService
{
    /**
     * Log a sensitive application action for non-repudiation and security auditing.
     *
     * @param string $action Nama aksi (e.g., 'DONATION_CREATE', 'PRODUCT_DELETE', 'ROLE_CHANGE')
     * @param array $details Detail payload terkait aksi
     * @param int|null $userId User ID yang melakukan aksi (jika ada)
     * @return void
     */
    public static function log(string $action, array $details = [], ?int $userId = null): void
    {
        $resolvedUserId = $userId ?? auth()->id() ?? 'guest';
        $ip = request()->ip() ?? '127.0.0.1';
        $userAgent = request()->userAgent() ?? 'unknown';

        $payload = [
            'timestamp'   => now()->toIso8601String(),
            'event'       => 'DEVSECOPS_SECURITY_AUDIT',
            'action'      => strtoupper($action),
            'user_id'     => $resolvedUserId,
            'ip_address'  => $ip,
            'user_agent'  => $userAgent,
            'details'     => $details,
        ];

        Log::channel('security')->info(json_encode($payload));
    }
}
