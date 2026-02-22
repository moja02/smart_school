@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fas fa-chalkboard me-2"></i> فصولي الدراسية</h2>
        <span class="badge bg-secondary fs-6">{{ $classes->count() }} فصول</span>
    </div>

    <div class="row g-4">
        @forelse($classes as $class)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center p-4">
                    <div class="avatar-lg bg-light text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <span class="h1 fw-bold mb-0">{{ substr($class->name, 0, 1) }}</span>
                    </div>
                    <h4 class="card-title fw-bold mb-2">{{ $class->name }}</h4>
                    <p class="text-muted small mb-4">
                        <i class="fas fa-users me-1"></i> {{ $class->students->count() }} طالب
                        | <i class="fas fa-layer-group me-1"></i> {{ $class->grade_level ?? 'عام' }}
                    </p>
                    
                    <a href="{{ route('teacher.class', $class->id) }}" class="btn btn-primary rounded-pill px-4 w-100">
                        <i class="fas fa-door-open me-2"></i> دخول الفصل
                    </a>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-3">
                    <a href="{{ route('teacher.attendance', $class->id) }}" class="text-secondary small text-decoration-none">
                        <i class="fas fa-clipboard-list me-1"></i> رصد الغياب
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="opacity-50 mb-3"><i class="fas fa-chalkboard-teacher fa-4x"></i></div>
            <h4 class="text-muted">لم يتم إسناد أي فصول لك حتى الآن.</h4>
        </div>
        @endforelse
    </div>
</div>

<style>
    .hover-card { transition: transform 0.2s; }
    .hover-card:hover { transform: translateY(-5px); }
</style>
@endsection