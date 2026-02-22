@extends('layouts.admin')

@section('content')
<div class="card page-header-card mb-4 shadow border-0 text-white" style="background: linear-gradient(135deg, #3a6073, #16222a);">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 text-white">تفضيلات المعلم: {{ $teacher->name }} ⚙️</h2>
            <p class="mb-0 opacity-75 text-white">حدد الأوقات التي "لا" يرغب المعلم بالتدريس فيها.</p>
        </div>
        <i class="fas fa-user-clock fa-4x opacity-25"></i>
    </div>
</div>

<form action="{{ route('admin.schedules.preferences.store', $teacher->id) }}" method="POST">
    @csrf
    <div class="card shadow border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>اليوم</th>
                            <th>إجازة كاملة؟</th>
                            <th>الحصص المرفوضة (اضغط على الحصة لحظرها)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                            @php
                                $pref = $preferences[$day] ?? null;
                                $isOff = $pref ? $pref->is_day_off : false;
                                $blocked = $pref ? ($pref->blocked_periods ?? []) : [];
                            @endphp
                            <tr>
                                <td class="fw-bold text-center bg-light">{{ $day }}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input day-off-toggle" type="checkbox" name="prefs[{{ $day }}][off]" value="1" data-index="{{ $loop->index }}" {{ $isOff ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2 p-group-{{ $loop->index }} {{ $isOff ? 'opacity-25' : '' }}">
                                        @foreach($periods as $p)
                                            <div class="form-check border rounded px-3 py-1">
                                                <input class="form-check-input p-check" type="checkbox" name="prefs[{{ $day }}][periods][{{ $p }}]" id="p_{{ $loop->parent->index }}_{{ $p }}" {{ in_array($p, $blocked) ? 'checked' : '' }} {{ $isOff ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-bold small" for="p_{{ $loop->parent->index }}_{{ $p }}">حصة {{ $p }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <button type="submit" class="btn btn-success px-5 rounded-pill fw-bold shadow-sm">حفظ التفضيلات</button>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.day-off-toggle').forEach((toggle, idx) => {
        toggle.addEventListener('change', function() {
            const group = document.querySelector('.p-group-' + idx);
            const checks = group.querySelectorAll('.p-check');
            if (this.checked) {
                group.classList.add('opacity-25');
                checks.forEach(c => { c.disabled = true; c.checked = false; });
            } else {
                group.classList.remove('opacity-25');
                checks.forEach(c => { c.disabled = false; });
            }
        });
    });
</script>
@endsection