@extends('layouts.student')
@section('content')
<h3 class="fw-bold mb-4">موادي الدراسية</h3>
<div class="row g-4">
    @foreach($subjects as $subject)
    <div class="col-md-4">
        <div class="card shadow-sm p-4 text-center">
            <h5 class="fw-bold">{{ $subject->name }}</h5>
            <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-primary mt-3">دخول المادة</a>
        </div>
    </div>
    @endforeach
</div>
@endsection