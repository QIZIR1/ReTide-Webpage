<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => (bool) DB::connection()->getPdo()),
            'cache' => $this->check(function () {
                Cache::put('health', 'ok', 5);
                return Cache::get('health') === 'ok';
            }),
            'storage' => $this->check(fn () => is_writable(storage_path())),
        ];

        $allOk = ! in_array('fail', $checks, true);

        return response()->json(
            array_merge(['status' => $allOk ? 'ok' : 'error'], $checks),
            $allOk ? 200 : 503
        );
    }

    private function check(callable $fn): string
    {
        try {
            return $fn() ? 'ok' : 'fail';
        } catch (\Throwable $e) {
            return 'fail';
        }
    }
}
