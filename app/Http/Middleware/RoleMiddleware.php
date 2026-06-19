<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        // التحقق من صلاحية المستخدم
        // ندعم طريقتين: عمود role المباشر، أو صلاحيات Spatie
        if (!Auth::user()->hasRole($role) && Auth::user()->role !== $role) {
            abort(403, 'ليس لديك صلاحية الوصول لهذه الصفحة.');
        }

        return $next($request);
    }
}