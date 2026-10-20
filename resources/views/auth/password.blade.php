@extends('layout')
@section('title','حسابي وكلمة المرور')
@section('subtitle','اختر كلمة مرور خاصة بك ولا تشارك الحساب مع أي مستخدم آخر.')
@section('content')
<div class="panel narrow"><div class="panel-head"><h2>تغيير كلمة المرور</h2><x-icon name="lock"/></div>
@if(auth()->user()->must_change_password)<div class="alert alert-warning">يلزم تغيير كلمة المرور المؤقتة قبل استخدام النظام.</div>@endif
<form action="{{ route('password.update') }}" method="post" class="form-stack">@csrf
<x-field name="current_password" type="password" label="كلمة المرور الحالية" required autocomplete="current-password" dir="ltr"/>
<x-field name="password" type="password" label="كلمة المرور الجديدة" required autocomplete="new-password" dir="ltr" hint="12 حرفاً على الأقل، وحروف كبيرة وصغيرة وأرقام ورموز."/>
<x-field name="password_confirmation" type="password" label="تأكيد كلمة المرور الجديدة" required autocomplete="new-password" dir="ltr"/>
<button class="btn btn-primary">حفظ كلمة المرور</button></form></div>
@endsection
