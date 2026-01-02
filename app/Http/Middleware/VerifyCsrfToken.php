<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'graphql',
    ];

    protected function tokensMatch($request)
    {
        $result = parent::tokensMatch($request);

        if (! $result) {
            Log::warning('CSRF token mismatch', [
                'session_token' => $request->session()->token(),
                'header_token' => $request->header('X-CSRF-TOKEN'),
                'cookie_token' => $request->cookie('XSRF-TOKEN'),
                'path' => $request->path(),
            ]);
        } else {
            Log::info('CSRF token matched', [
                'path' => $request->path(),
            ]);
        }

        return $result;
    }
}

