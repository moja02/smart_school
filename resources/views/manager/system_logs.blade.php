@extends('layouts.manager')

@section('content')
<div class="container-fluid py-4">
    
    <div class="card page-header-card mb-4 shadow border-0 bg-dark text-white">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1 text-white">سجل النظام (Tracking) 🕵️‍♂️</h2>
                <p class="mb-0 opacity-75">
                    مراقبة جميع الحركات والتعديلات التي تتم في النظام لحظة بلحظة.
                </p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-history fa-4x opacity-25 text-white"></i>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i> أحدث الحركات</h6>
        </div>
        <div class="card-body p-0">
            {{-- أضف هذا الجزء فوق الكارد الخاص بالجدول مباشرة --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form action="{{ route('manager.system_logs') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">القسم (الجدول)</label>
                        <select name="log_name" class="form-select border-0 bg-light">
                            <option value="">كل الأقسام</option>
                            @foreach($logNames as $name)
                                <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">نوع الحركة</label>
                        <select name="event" class="form-select border-0 bg-light">
                            <option value="">كل الحركات</option>
                            <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>إضافة</option>
                            <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>تعديل</option>
                            <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>حذف</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter me-1"></i> تصفية النتائج
                        </button>
                        <a href="{{ route('manager.system_logs') }}" class="btn btn-outline-secondary ms-2"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="py-3 px-4">التاريخ والوقت</th>
                            <th>المستخدم (الفاعل)</th>
                            <th>نوع الحركة</th>
                            <th>القسم (الجدول)</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="py-3 px-4">
                                <span class="fw-bold d-block">{{ $log->created_at->format('Y-m-d') }}</span>
                                <small class="text-muted"><i class="far fa-clock me-1"></i> {{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            
                            <td>
                                @if($log->causer)
                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                        <i class="fas fa-user me-1"></i> {{ $log->causer->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">نظام (System)</span>
                                @endif
                            </td>
                            
                            <td>
                                @if($log->event === 'created')
                                    <span class="badge bg-success"><i class="fas fa-plus me-1"></i> إضافة</span>
                                @elseif($log->event === 'updated')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-edit me-1"></i> تعديل</span>
                                @elseif($log->event === 'deleted')
                                    <span class="badge bg-danger"><i class="fas fa-trash me-1"></i> حذف</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $log->event }}</span>
                                @endif
                            </td>
                            
                            <td>
                                {{-- تنظيف اسم المودل ليكون مقروءاً --}}
                                <span class="text-muted fw-bold">{{ class_basename($log->subject_type) }}</span>
                                @if($log->subject_id)
                                    <small class="text-muted">(ID: {{ $log->subject_id }})</small>
                                @endif
                            </td>
                            
                            <td>
                                @if($log->properties->isNotEmpty())
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $log->id }}" aria-expanded="false">
                                        <i class="fas fa-eye"></i> عرض
                                    </button>
                                @else
                                    <span class="text-muted small">لا توجد تفاصيل</span>
                                @endif
                            </td>
                        </tr>
                        
                        {{-- صف مخفي لعرض التفاصيل (التغييرات) --}}
                        @if($log->properties->isNotEmpty())
                        <tr class="collapse bg-light" id="details-{{ $log->id }}">
                            <td colspan="5" class="p-4 border-bottom">
                                <div class="row">
                                    @if(isset($log->properties['old']))
                                    <div class="col-md-6 border-end">
                                        <h6 class="text-danger fw-bold"><i class="fas fa-minus-circle"></i> البيانات القديمة:</h6>
                                        <pre class="bg-white p-3 rounded shadow-sm border" style="direction: ltr; text-align: left;">{{ json_encode($log->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                    
                                    @if(isset($log->properties['attributes']))
                                    <div class="col-md-6">
                                        <h6 class="text-success fw-bold"><i class="fas fa-plus-circle"></i> البيانات الجديدة:</h6>
                                        <pre class="bg-white p-3 rounded shadow-sm border" style="direction: ltr; text-align: left;">{{ json_encode($log->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif

                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <h5>لا توجد حركات مسجلة حتى الآن</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white py-3">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection