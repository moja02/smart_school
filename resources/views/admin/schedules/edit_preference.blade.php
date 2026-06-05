@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
        <div class="card-body p-4">
            <h3 class="fw-bold mb-1"><i class="fas fa-user-clock me-2"></i> ضبط أوقات المعلم: {{ $teacher->name }}</h3>
            <p class="mb-0 opacity-75">حدد أيام العطل والحصص المحظورة التي لا يمكن للمعلم التدريس فيها.</p>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <form action="{{ route('admin.schedules.update_preference', $teacher->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>اليوم</th>
                                <th>إجازة كاملة (يوم عطلة)</th>
                                <th>الحصص المحظورة (في حال كان حاضراً)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
                            @endphp
                            
                            @foreach($days as $day)
                                @php
                                    $pref = $teacher->preferences->where('day_name', $day)->first();
                                    $isDayOff = $pref ? $pref->is_day_off : false;
                                    $blocked = $pref ? ($pref->blocked_periods ?? []) : [];
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $day }}</td>
                                    
                                    <td>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input day-off-switch" type="checkbox" name="preferences[{{ $day }}][is_day_off]" value="1" id="off_{{ $loop->index }}" {{ $isDayOff ? 'checked' : '' }} style="width: 2.5em; height: 1.2em;">
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 flex-wrap periods-container" id="periods_{{ $loop->index }}" style="{{ $isDayOff ? 'opacity: 0.5; pointer-events: none;' : '' }}">
                                            @for($i = 1; $i <= 7; $i++)
                                                <div class="form-check form-check-inline m-0">
                                                    <input class="form-check-input" type="checkbox" name="preferences[{{ $day }}][blocked_periods][]" value="{{ $i }}" id="block_{{ $day }}_{{ $i }}" {{ in_array($i, $blocked) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="block_{{ $day }}_{{ $i }}">{{ $i }}</label>
                                                </div>
                                            @endfor
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.schedules.preferences') }}" class="btn btn-light border fw-bold px-4">إلغاء الرجوع</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-save me-2"></i> حفظ التفضيلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // سكريبت بسيط: لو المدير اختار إن اليوم عطلة، يقفل تحديد الحصص المحظورة لأن اليوم كله عطلة
    document.querySelectorAll('.day-off-switch').forEach((switchEl, index) => {
        switchEl.addEventListener('change', function() {
            const periodsContainer = document.getElementById('periods_' + index);
            if(this.checked) {
                periodsContainer.style.opacity = '0.5';
                periodsContainer.style.pointerEvents = 'none';
                // إزالة التحديد عن الحصص
                periodsContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            } else {
                periodsContainer.style.opacity = '1';
                periodsContainer.style.pointerEvents = 'auto';
            }
        });
    });
</script>
@endsection