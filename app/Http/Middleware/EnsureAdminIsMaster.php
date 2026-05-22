<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsMaster
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if (!$admin || $admin->role !== 'master') {
            abort(403, 'Apenas usuários master podem acessar este recurso.');
        }

        return $next($request);
    }
}

