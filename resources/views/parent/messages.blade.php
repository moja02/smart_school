@extends('layouts.parent')

@section('title', 'رسائل المدرسة')

@section('content')

    <div class="page-header-card p-4 text-white shadow mb-4">
        <h3 class="mb-1">رسائل المدرسة</h3>
        <p class="mb-0">
            رسائل وتنبيهات من الادارة والمعلمين.
        </p>
    </div>

    @if(isset($messages) && $messages->count())
        <div class="list-group shadow-sm rounded-4 mb-4">
            @foreach($messages as $msg)
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-1">{{ $msg->subject ?? 'بدون عنوان' }}</h5>
                        <small class="text-muted">
                            {{ $msg->created_at ? $msg->created_at->format('Y-m-d H:i') : '' }}
                        </small>
                    </div>
                    <p class="mb-1 text-muted">
                        من: {{ $msg->sender_name ?? 'مجهول' }}
                        @if(!empty($msg->sender_role))
                            <span class="badge bg-secondary ms-2">
                                {{ $msg->sender_role }}
                            </span>
                        @endif
                    </p>
                    <p class="mb-0">
                        {{ $msg->body ?? '' }}
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            لا توجد رسائل لعرضها حاليا.
        </div>
    @endif

    {{-- نموذج بسيط لارسال رسالة (ربطه لاحقا بالكنترولر) --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0">
            <strong>ارسال رسالة الى المدرسة</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('parent.messages') }}" method="POST">
                @csrf
                {{-- ستحتاج لتعديل route الى route آخر POST عندما تضيفه في web.php --}}

                <div class="mb-3">
                    <label class="form-label">العنوان</label>
                    <input type="text" name="subject" class="form-control"
                           placeholder="مثال: استفسار عن مستوى ابني">
                </div>

                <div class="mb-3">
                    <label class="form-label">الرسالة</label>
                    <textarea name="body" rows="4" class="form-control"
                              placeholder="اكتب رسالتك هنا..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    ارسال الرسالة
                </button>
            </form>
        </div>
    </div>

@endsection
