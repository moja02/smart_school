@extends('layouts.teacher')

@section('content')
<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-user-check me-2"></i> رصد الغياب: {{ $class->name }}
            </h5>
            <span class="badge bg-light text-dark">{{ $date }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.attendance.store', $class->id) }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>الطالب</th>
                                <th class="text-center text-success">حاضر (Present)</th>
                                <th class="text-center text-danger">غائب (Absent)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class->students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $student->user->name }}</td>
                                <td class="text-center">
                                    <div class="form-check d-inline-block">
                                        <input class="form-check-input" type="radio" 
                                               name="attendance[{{ $student->id }}]" 
                                               value="1" 
                                               id="pres_{{ $student->id }}"
                                               {{ (isset($attendance[$student->id]) && $attendance[$student->id] == 1) ? 'checked' : 'checked' }}>
                                        <label class="form-check-label text-success" for="pres_{{ $student->id }}">حاضر</label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check d-inline-block">
                                        <input class="form-check-input" type="radio" 
                                               name="attendance[{{ $student->id }}]" 
                                               value="0" 
                                               id="abs_{{ $student->id }}"
                                               {{ (isset($attendance[$student->id]) && $attendance[$student->id] == 0) ? 'checked' : '' }}>
                                        <label class="form-check-label text-danger" for="abs_{{ $student->id }}">غائب</label>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5 rounded-pill shadow">
                        <i class="fas fa-save me-2"></i> حفظ الغياب
                    </button>
                    <a href="{{ route('teacher.class', $class->id) }}" class="btn btn-secondary rounded-pill px-4 ms-2">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
