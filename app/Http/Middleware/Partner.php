<?php

namespace App\Http\Middleware;

use App\Enum\AgentStatus;
use Closure;
use App\Enum\UserType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Partner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user() && auth()->user()->type == UserType::Agent && auth()->user()->agent->status == AgentStatus::Approve) {
            return $next($request);
        } else {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('partner/login');
        }
    }
}
