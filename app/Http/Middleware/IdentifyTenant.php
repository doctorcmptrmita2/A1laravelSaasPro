<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * Identify and set the tenant for the authenticated user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->tenant_id) {
                // Set tenant in request for easy access
                $request->merge(['tenant_id' => $user->tenant_id]);
                
                // Set tenant in session
                session(['tenant_id' => $user->tenant_id]);
            }
        }

        return $next($request);
    }
}
