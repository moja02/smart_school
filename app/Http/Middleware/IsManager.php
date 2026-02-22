<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsManager
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. هل المستخدم مسجل دخول؟
        // 2. هل دوره في قاعدة البيانات هو 'manager'؟
        if (auth()->check() && auth()->user()->role === 'manager') {
            return $next($request);
        }

        // إذا لم يكن مديراً، أوقفه فوراً (403 Forbidden)
        abort(403, 'عذراً، هذه المنطقة مخصصة لمدير المدرسة فقط.');
    }
}