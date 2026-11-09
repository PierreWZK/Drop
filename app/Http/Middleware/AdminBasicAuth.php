<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = (string) config('drop.admin_user');
        $password = (string) config('drop.admin_password');

        if ($user === '' || $password === '') {
            abort(503, 'ADMIN_USER et ADMIN_PASSWORD non définis dans .env');
        }

        if ($request->getUser() !== $user || ! hash_equals($password, (string) $request->getPassword())) {
            return response('Accès refusé', 401, ['WWW-Authenticate' => 'Basic realm="Drop admin"']);
        }

        return $next($request);
    }
}
