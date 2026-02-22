<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSchoolAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // أدوار الإدارة المدرسية
        $allowed = ['principal','vice_principal','admin_staff'];

        // المطوّر (الأدمن) يتجاوز
        $isDeveloper = $user && $user->role === 'developer';

        if (!$user || (!$isDeveloper && !in_array($user->role, $allowed))) {
            abort(403, 'غير مصرح: هذه المنطقة مخصصة لإدارة المدرسة.');
        }
        return $next($request);
    }
}
