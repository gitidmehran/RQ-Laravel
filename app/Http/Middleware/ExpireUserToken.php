<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;

class ExpireUserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            $lastActivity = Carbon::parse($user->last_activity);
            $idleTimeLimit = Carbon::now()->subHours(2);
            // $idleTimeLimit = Carbon::now()->subMinutes(10);

            if ($lastActivity->lt($idleTimeLimit)) {
                $user->tokens()->delete();
                return response()->json(['error' => 'Token expired due to inactivity'], 401);
            }

            $user->last_activity = Carbon::now();
            $user->save();
        }

        return $next($request);
    }
}
