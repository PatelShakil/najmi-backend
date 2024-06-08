<?php

namespace App\Http\Middleware;

use App\Models\AdminMst;
use App\Models\WorkerMst;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectAPIRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!str_contains($request->path(), "/login")) {
            $token = $request->header('token');
            if (AdminMst::where("token", $token)->exists()) {
                return $next($request);
            } else if (WorkerMst::where("token", $token)->exists()) {
                return $next($request);
            } else {
                return response()->json(["message" => "Unauthorised access"], 401);
            }
        } else {
            return $next($request);
        }
    }
}
