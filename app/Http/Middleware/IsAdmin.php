<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check jika user tidak login
        if (!auth()->check()) {
            return response()->view('errors.unauthorized', ['message' => 'Anda harus login terlebih dahulu'], 401);
        }

        // Get authenticated user
        $user = auth()->user();
        
        // Verify user memiliki is_admin column dan nilainya true
        if (!$user || !isset($user->is_admin) || $user->is_admin !== 1) {
            // Log unauthorized access attempt
            \Log::warning("Unauthorized admin access attempt by user: " . ($user ? $user->id : 'unknown'));
            
            abort(403, 'Anda tidak memiliki izin akses admin panel');
        }

        return $next($request);
    }
}
