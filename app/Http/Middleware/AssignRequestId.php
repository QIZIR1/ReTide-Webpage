<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->header('X-Request-ID') ?: (string) Str::uuid();
        Log::withContext(['request_id' => $id]);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $id);

        return $response;
    }
}
