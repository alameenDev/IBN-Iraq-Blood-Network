@extends('layout')
@section('title','المستخدمون')
@section('subtitle','حساب مستقل لكل موظف، مرتبط بمؤسسة ودور واضح.')
@section('actions')@if(auth()->user()->allowed('users.manage'))<a class="btn btn-primary" href="{{ route('users.create') }}"><x-icon name="plus"/>إضافة مستخدم</a>@endif@endsection
@section('content')<div class="panel"><div class="table-wrap"><table><thead><tr><th>المستخدم</th><th>المؤسسة</th><th>الدور</th><th>الحالة</th><th>إجراء</th></tr></thead><tbody>
@forelse($users as $u)<tr><td><strong>{{ $u->name }}</strong><small dir="ltr">{{ $u->email }}</small></td><td>{{ $u->facility?->name??'الإدارة الوطنية' }}</td><td>{{ $u->role->name }}</td><td><x-status :value="$u->active?'active':'suspended'"/>@if($u->must_change_password)<small>يلزم تغيير كلمة المرور</small>@endif</td><td>@if(auth()->user()->allowed('users.manage'))<a class="btn btn-light btn-sm" href="{{ route('users.edit',$u) }}">إدارة الحساب</a>@endif</td></tr>@empty<tr><td colspan="5"><div class="empty-state">لا توجد حسابات ضمن هذا النطاق.</div></td></tr>@endforelse
</tbody></table></div><x-pager :items="$users"/></div>@endsection
