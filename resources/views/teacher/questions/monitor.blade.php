@extends('layouts.teacher')
@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold m-0">رصد الدرجات: {{ $assessment->title }}</h5>
        <p class="text-muted mb-0">{{ $subject->name }} - {{ $class->name }}</p>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.assessments.store_grades', ['subject_id' => $subject->id, 'class_id' => $class->id, 'assessment_id' => $assessment->id]) }}" method="POST">
            @csrf
            <table class="table table-bordered">
                <thead><tr><th>الطالب</th><th>الدرجة (من {{ $assessment->max_score }})</th></tr></thead>
                <tbody>
                    @forelse($students as $student)
                    @php $mark = $student->assessmentMarks->first() ? $student->assessmentMarks->first()->score : ''; @endphp
                    <tr>
                        <td>{{ $student->user->name }}</td>
                        <td>
                            <input type="number" step="0.5" name="grades[{{ $student->id }}]" class="form-control" value="{{ $mark }}" max="{{ $assessment->max_score }}">
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="2">لا يوجد طلاب.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">حفظ الدرجات</button>
        </form>
    </div>
</div>
@endsectionl