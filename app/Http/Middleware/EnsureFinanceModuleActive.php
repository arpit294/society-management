<?php

namespace App\Http\Middleware;

use App\Helpers\ModuleHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFinanceModuleActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! ModuleHelper::isFinanceActive()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'premium' => true,
                    'message' => 'The Finance & Accounting module is currently inactive. Please enable this module to access this feature.',
                ], 403);
            }

            return response()->view('finance.locked', [
                'requestedUrl' => $request->path(),
            ], 403);
        }

        return $next($request);
    }
}
