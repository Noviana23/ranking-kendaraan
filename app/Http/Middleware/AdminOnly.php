<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->session()->get('user_role') === 'admin', 403, 'Fitur ini hanya dapat diakses Admin.');
        return $next($request);
    }
}
