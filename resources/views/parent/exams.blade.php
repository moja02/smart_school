@extends('layouts.parent')
@section('content')

{{-- 1. الترويسة (اللون الداكن المعتاد للنظام) --}}
<div class="card page-header-card mb-4 shadow border-0 bg-dark text-white" style="border-radius: 1rem;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">جدول امتحانات الأبناء 📅</h2>
            <p class="text-white-50 mb-0">متابعة مواعيد الامتحانات القادمة لأبنائك بدقة.</p>
        </div>
        <div>
            <a href="{{ route('parent.dashboard') }}" class="btn btn-light text-dark rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-right me-2"></i> عودة
            </a>
        </div>
    </div>
</div>

{{-- 2. شبكة الأيام (التقويم) --}}
<div class="row g-3">
    @foreach($calendarDays as $day)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
            
            @if($day['has_exam'])
                {{-- تصميم اليوم الذي يحتوي على امتحان --}}
                <div class="card h-100 shadow border-0 hover-scale border-top border-4 border-danger bg-white" style="border-radius: 1rem;">
                    <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                        <h6 class="fw-bold text-danger mb-1"><i class="fas fa-calendar-day me-1"></i> {{ $day['day_name'] }}</h6>
                        <h2 class="fw-bold text-dark mb-1">{{ $day['day_num'] }}</h2>
                        <small class="text-muted d-block mb-2">{{ $day['month_name'] }}</small>
                        <hr class="my-2 opacity-25">
                        
                        {{-- طباعة أسماء المواد التي بها امتحانات واسم الابن --}}
                        @foreach($day['exams'] as $exam)
                            <div class="badge bg-danger rounded-pill w-100 py-2 mb-1 text-wrap shadow-sm text-start ps-3">
                                <i class="fas fa-exclamation-circle me-1 text-warning"></i> 
                                <span class="fw-bold">{{ $exam->subject->name }}</span>
                                <small class="d-block text-white-50 mt-1" style="font-size: 0.7rem;">
                                    <i class="fas fa-user-graduate me-1"></i> {{ mb_substr($exam->child_name, 0, 15) }}..
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- تصميم الأيام العادية (لا توجد امتحانات) --}}
                <div class="card h-100 shadow-sm border-0 bg-light" style="border-radius: 1rem; opacity: 0.7;">
                    <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                        <h6 class="fw-bold text-muted mb-1">{{ $day['day_name'] }}</h6>
                        <h3 class="fw-bold text-secondary mb-1">{{ $day['day_num'] }}</h3>
                        <small class="text-muted d-block">{{ $day['month_name'] }}</small>
                    </div>
                </div>
            @endif

        </div>
    @endforeach
</div>

<style>
    .hover-scale { transition: transform 0.2s ease-in-out, box-shadow 0.2s; }
    .hover-scale:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>
@endsection