@extends('layouts.parent')
@section('title', 'الواجبات المدرسية')
@section('content')
<div class="container-fluid">
    <h3 class="mb-4">المتابعة اليومية للواجبات المدرسية</h3>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($homeworks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>المادة</th>
                            <th>تفاصيل الواجب</th>
                            <th>تاريخ التسليم</th>
                            <th>المعلم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($homeworks as $hw)
                        <tr>
                            <td><span class="badge bg-info text-dark fs-6">{{ $hw->subject->name ?? 'غير معروف' }}</span></td>
                            <td>
                                <strong>{{ $hw->title }}</strong><br>
                                <small class="text-muted">{{ $hw->description }}</small>
                            </td>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($hw->due_date)->format('Y-m-d') }}</strong><br>
                                @if(\Carbon\Carbon::parse($hw->due_date)->isPast())
                                    <span class="badge bg-danger">موعد منتهي</span>
                                @else
                                    <span class="badge bg-success">متاح للتسليم</span>
                                @endif
                            </td>
                            <td>{{ $hw->teacher->name ?? 'غير معروف' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center p-5 text-muted bg-light rounded">
                <i class="fas fa-clipboard-check fs-1 mb-3 text-secondary"></i>
                <h5>لا توجد واجبات مطلوبة حالياً</h5>
                <p>جميع الأبناء قاموا بإنهاء المهام المطلوبة، أو لم يتم تكليفهم بأي واجبات جديدة.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
