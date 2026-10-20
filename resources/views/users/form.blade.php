@extends('layout')
@section('title',$account->exists?'إدارة حساب مستخدم':'إضافة مستخدم')
@section('subtitle','المؤسسة تحدد نطاق البيانات، والدور يحدد العمليات المسموح بها.')
@section('content')<form action="{{ $account->exists?route('users.update',$account):route('users.store') }}" method="post" class="panel form-panel">@csrf @if($account->exists)@method('put')@endif
<div class="form-grid"><x-field name="name" label="اسم الموظف" :value="$account->name" required maxlength="120"/>
<x-field name="email" type="email" label="البريد الإلكتروني" :value="$account->email" required maxlength="180" dir="ltr"/>
<label class="field"><span>الدور <b class="required">*</b></span><select name="role_id" required data-role-selector><option value="">اختر الدور</option>@foreach($roles as $role)<option value="{{ $role->id }}" data-scope="{{ $role->scope }}" @selected((string)old('role_id',$account->role_id)===(string)$role->id)>{{ $role->name }} — {{ \App\Support\Catalog::label($role->scope) }}</option>@endforeach</select></label>
@if(auth()->user()->national())<div data-facility-field><x-select name="facility_id" label="المؤسسة المرتبطة" :options="$facilities->pluck('name','id')->all()" :value="$account->facility_id"/></div>@else<div class="field"><span>المؤسسة</span><div class="readonly-value">{{ auth()->user()->facility->name }}</div></div>@endif
<x-select name="active" label="حالة الحساب" :options="[1=>'فعال',0=>'موقوف']" :value="$account->exists?(int)$account->active:1" required/>
</div><div class="section-heading"><span><x-icon name="lock"/></span><div><h2>{{ $account->exists?'إعادة ضبط كلمة المرور — اختياري':'كلمة المرور المؤقتة' }}</h2><p>12 حرفاً على الأقل، بحروف كبيرة وصغيرة وأرقام ورموز. تُسلّم للموظف عبر قناة آمنة.</p></div></div>
<div class="form-grid"><x-field name="password" type="password" label="كلمة المرور" :required="!$account->exists" autocomplete="new-password" dir="ltr"/><x-field name="password_confirmation" type="password" label="تأكيد كلمة المرور" :required="!$account->exists" autocomplete="new-password" dir="ltr"/></div>
<div class="alert alert-info">الحساب الجديد أو الذي أعيد ضبطه يغيّر كلمة مروره عند أول دخول. تعديل الدور أو إيقاف الحساب ينهي الجلسات السابقة.</div>
<div class="form-actions"><button class="btn btn-primary">حفظ الحساب</button><a class="btn btn-light" href="{{ route('users.index') }}">رجوع</a></div></form>@endsection
