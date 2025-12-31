<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscription
{
    /**
     * Handle an incoming request.
     *
     * Ensure the user has an active subscription.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!$user->tenant_id) {
            abort(403, 'No tenant associated with this user.');
        }

        $tenant = $user->tenant;
        
        if (!$tenant) {
            abort(403, 'Tenant not found.');
        }

        // Check if tenant is active
        if (!$tenant->is_active) {
            abort(403, 'Your account has been deactivated.');
        }

        // Check for active subscription
        $subscription = $tenant->activeSubscription;
        
        if (!$subscription && !$tenant->trial_ends_at?->isFuture()) {
            // No active subscription and trial expired
            return redirect()->route('subscription.required')
                ->with('error', 'An active subscription is required to access this feature.');
        }

        return $next($request);
    }
}
