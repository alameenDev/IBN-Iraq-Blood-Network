@extends('layout')
@section('title','ربط المستشفيات')
@section('subtitle','ربط موثق بين مصرف رئيسي ومستشفى أو مصرف فرعي، قبل السماح بطلب الدم.')
@section('content')
<div class="flow-strip"><span><b>1</b>طلب ربط</span><i>←</i><span><b>2</b>موافقة الطرف الآخر أو الإدارة</span><i>←</i><span><b>3</b>إتاحة طلب الدم</span></div>
@if(auth()->user()->allowed('links.request'))
<details class="panel expandable" @if($errors->any()) open @endif><summary><x-icon name="plus"/>إنشاء طلب ربط جديد</summary><form action="{{ route('links.store') }}" method="post" class="form-stack">@csrf
<div class="form-grid"><x-select name="bank_id" label="المصرف الرئيسي" :options="$banks->pluck('name','id')->all()" required/>
<x-select name="hospital_id" label="المستشفى أو المصرف الفرعي" :options="$hospitals->pluck('name','id')->all()" required/>
@if(auth()->user()->national())<x-select name="origin" label="الجهة التي يُسجل الطلب بالنيابة عنها" :options="['hospital'=>'المستشفى','bank'=>'مصرف الدم']" value="hospital" required/>@endif
<x-field name="note" label="مرجع الاتفاق أو سبب طلب الربط" required maxlength="500"/>
</div><p class="form-note">الحساب المحلي يمكنه طلب ربط مؤسسته فقط. منشئ الطلب لا يعتمد طلبه بنفسه.</p><button class="btn btn-primary">إرسال طلب الربط</button></form></details>
@endif
<div class="panel"><div class="panel-head"><h2>روابط الشبكة</h2><span class="count-chip">{{ $links->total() }} رابط</span></div><div class="table-wrap"><table><thead><tr><th>المصرف الرئيسي</th><th>المستشفى / المصرف الفرعي</th><th>حالة الربط</th><th>مراجعة</th></tr></thead><tbody>
@forelse($links as $link)<tr><td><strong>{{ $link->bank->name }}</strong><small>{{ $link->bank->governorate }}</small></td><td><strong>{{ $link->hospital->name }}</strong><small>{{ $link->hospital->governorate }}</small></td><td><x-status :value="$link->status"/><small>{{ $link->note }}</small>@if($link->decision_reason)<small>القرار: {{ $link->decision_reason }}</small>@endif</td><td>
@if(auth()->user()->allowed('links.review') && (($link->status==='pending' && $link->requested_by!==auth()->id() && (auth()->user()->national()||auth()->user()->facility_id!==$link->requested_facility_id)) || $link->status==='active'))
<details class="action-details"><summary>مراجعة الرابط</summary><form method="post" action="{{ route('links.review',$link) }}">@csrf
<x-select name="status" label="القرار" :options="$link->status==='active'?['suspended'=>'إيقاف الربط']:['active'=>'اعتماد الربط','rejected'=>'رفض الربط']" required/>
<x-field name="reason" label="سبب القرار / مرجع الموافقة" required maxlength="500"/><button class="btn btn-primary btn-sm">تسجيل القرار</button></form></details>
@else<span class="muted">{{ $link->status==='pending'?'بانتظار المخول بالموافقة':'لا يوجد إجراء متاح' }}</span>@endif
</td></tr>@empty<tr><td colspan="4"><div class="empty-state"><x-icon name="network"/><strong>لا توجد روابط مسجلة</strong><p>أنشئ المؤسسات وفعلها، ثم ابدأ طلب ربط جديد.</p></div></td></tr>@endforelse</tbody></table></div><x-pager :items="$links"/></div>
@endsection
