<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('login');
        }

        // Check if user has the required permission
        if (!Auth::user()->can($permission)) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized access attempt', [
                'user' => Auth::user()->id,
                'permission' => $permission,
                'url' => $request->fullUrl(),
            ]);

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Permission Denied',
                    'message' => 'You do not have permission to perform this action.'
                ], 403);
            }

            // Redirect to maintenance dashboard for regular requests
            return redirect()->route('maintenance.dashboard')->with('error', 'You do not have permission to perform this action');
        }

        return $next($request);
    }
}
