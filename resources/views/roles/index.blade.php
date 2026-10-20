@extends('layout')
@section('title','الأدوار والصلاحيات')
@section('subtitle','الصلاحيات مفروضة من الخادم، وليست مجرد إخفاء للأزرار.')
@section('content')
<div class="alert alert-info">الربط بين المؤسسات لا يفتح سجلات المتبرعين. الصلاحيات الطبية لا تُمنح تلقائياً لمدير الشبكة.</div>
@if(auth()->user()->allowed('roles.manage'))<details class="panel expandable" @if($errors->any()) open @endif><summary><x-icon name="plus"/>إنشاء دور مخصص للمؤسسة</summary>
<form method="post" action="{{ route('roles.store') }}">@csrf<x-field name="name" label="اسم الدور" required maxlength="120"/>
<div class="permission-grid">@foreach($permissions->where('facility_allowed',true) as $p)<label class="check-row"><input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked(in_array($p->id,old('permissions',[])))><span>{{ $p->name }}</span></label>@endforeach</div>
<button class="btn btn-primary">إنشاء الدور</button></form></details>@endif
<div class="roles-grid">@foreach($roles as $role)<section class="panel role-card"><div class="panel-head"><div><h2>{{ $role->name }}</h2><p>{{ \App\Support\Catalog::label($role->scope) }} · {{ $role->is_system?'دور نظامي ثابت':'دور مخصص' }}</p></div><x-icon name="shield"/></div>
<div class="permission-tags">@foreach($role->permissions as $p)<span>{{ $p->name }}</span>@endforeach</div>
@if(!$role->is_system && auth()->user()->allowed('roles.manage'))<details class="action-details"><summary>تعديل صلاحيات الدور</summary><form method="post" action="{{ route('roles.update',$role) }}">@csrf @method('put')<x-field name="name" label="اسم الدور" :value="$role->name" required/>
@foreach($permissions->where('facility_allowed',true) as $p)<label class="check-row"><input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked($role->permissions->contains('id',$p->id))><span>{{ $p->name }}</span></label>@endforeach<button class="btn btn-primary btn-sm">حفظ الصلاحيات</button></form></details>@endif
</section>@endforeach</div>@endsection
