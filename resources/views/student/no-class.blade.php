@extends('layouts.student')

@section('content')
<div class="container py-5 text-center">
    <div class="card shadow border-0 p-5 mx-auto" style="max-width: 600px;">
        <div class="text-warning mb-3">
            <i class="fas fa-exclamation-triangle fa-5x"></i>
        </div>
        <h2 class="fw-bold">حسابك غير مرتبط بفصل دراسي!</h2>
        <p class="text-muted lead mt-3">
            عذراً، لا يمكنك تصفح المواد الدراسية لأنك لم تُضاف إلى أي فصل دراسي بعد.
        </p>
        <p class="mb-4">يرجى التواصل مع إدارة المدرسة لتسكينك في فصلك الصحيح.</p>
        <a href="{{ route('login.form') }}" class="btn btn-primary rounded-pill px-4">تسجيل الخروج</a>
    </div>
</div>
@endsection