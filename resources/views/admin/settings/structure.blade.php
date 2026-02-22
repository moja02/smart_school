@extends('layouts.admin')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2"></i> هيكلية المدرسة (تحديد المراحل)</h5>
    </div>
    <div class="card-body p-4">
        <p class="text-muted">حدد المراحل الدراسية المتوفرة في مدرستك فقط. سيتم إخفاء باقي المراحل من الجداول والتقارير.</p>
        
        <form action="{{ route('admin.settings.structure.update') }}" method="POST">
            @csrf
            
            <div class="row">
                @php
                    $stages = [
                        'primary' => ['name' => 'المرحلة الابتدائية', 'color' => 'success'],
                        'middle' => ['name' => 'المرحلة الإعدادية', 'color' => 'warning'],
                        'secondary' => ['name' => 'المرحلة الثانوية', 'color' => 'danger']
                    ];
                @endphp

                @foreach($stages as $key => $info)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-{{ $info['color'] }} shadow-sm">
                            <div class="card-header bg-{{ $info['color'] }} text-white fw-bold text-center">
                                {{ $info['name'] }}
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    @foreach($allGrades->where('stage', $key) as $grade)
                                        <li class="list-group-item d-flex align-items-center">
                                            <div class="form-check form-switch w-100">
                                                <input class="form-check-input" type="checkbox" name="grades[]" 
                                                       value="{{ $grade->id }}" id="grade_{{ $grade->id }}"
                                                       {{ in_array($grade->id, $activeGradeIds) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold mx-2" for="grade_{{ $grade->id }}">
                                                    {{ $grade->name }}
                                                </label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <hr>
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-save me-2"></i> حفظ الهيكلية
            </button>
        </form>
    </div>
</div>
@endsection