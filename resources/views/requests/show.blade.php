@extends('layout')
@section('title','تفاصيل طلب الدم')
@section('subtitle',$record->reference)
@section('actions')<x-status :value="$record->status"/>@endsection
@section('content')<div class="detail-grid"><section class="panel"><div class="panel-head"><h2>بيانات الطلب</h2><x-icon name="requests"/></div><dl class="detail-list">
<dt>المستشفى</dt><dd>{{ $record->hospital->name }}</dd><dt>المصرف المستلم</dt><dd>{{ $record->bank->name }}</dd>
<dt>نوع الطلب</dt><dd>{{ \App\Support\Catalog::label($record->request_type) }}</dd><dt>المكون</dt><dd>{{ \App\Support\Catalog::label($record->component) }}</dd><dt>الفصيلة المطلوبة</dt><dd dir="ltr">{{ \App\Support\Catalog::label($record->blood_group) }}</dd><dt>الوحدات المطلوبة</dt><dd>{{ $record->quantity }}</dd><dt>الأولوية</dt><dd><x-status :value="$record->priority"/></dd><dt>موعد الاحتياج</dt><dd>{{ $record->needed_at->timezone('Asia/Baghdad')->format('Y/m/d H:i') }}</dd>
@if(!auth()->user()->national() && $record->request_type==='patient')<dt>مرجع المريض الداخلي</dt><dd>{{ $record->patient_reference }}</dd>@endif
@if($record->decision_reason)<dt>سبب آخر قرار</dt><dd>{{ $record->decision_reason }}</dd>@endif</dl></section>
<section class="panel"><div class="panel-head"><h2>إجراءات الطلب</h2><x-icon name="shield"/></div><div class="alert alert-info">قبول الطلب هنا إداري فقط. لا يتم اختيار أكياس أو فحص المطابقة أو صرف الدم.</div>
@if(auth()->user()->allowed('requests.review') && auth()->user()->facility_id===$record->bank_id && $record->status==='submitted')<form method="post" action="{{ route('requests.review',$record) }}" class="form-stack">@csrf<x-select name="status" label="القرار الإداري" :options="['accepted'=>'قبول إداري — بدون صرف','rejected'=>'رفض الطلب']" required/><x-field name="reason" label="سبب القرار" required maxlength="500"/><button class="btn btn-primary">تسجيل القرار</button></form>@endif
@if(auth()->user()->allowed('requests.create') && auth()->user()->facility_id===$record->hospital_id && in_array($record->status,['submitted','accepted']))<form method="post" action="{{ route('requests.cancel',$record) }}" class="form-stack" data-confirm="هل تريد إلغاء الطلب؟ سيبقى تاريخه محفوظاً.">@csrf<x-field name="reason" label="سبب إلغاء الطلب" required maxlength="500"/><button class="btn btn-danger">إلغاء الطلب</button></form>@endif
<a class="btn btn-light" href="{{ route('requests.index') }}">العودة إلى الطلبات</a></section></div>@endsection
