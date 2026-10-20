@extends('layout')
@section('title','المؤسسات والمستشفيات')
@section('subtitle','دليل الجهات، تبعيتها الإدارية، وحالة تفعيل كل مؤسسة.')
@section('actions')@if(auth()->user()->allowed('facilities.manage'))<a class="btn btn-primary" href="{{ route('facilities.create') }}"><x-icon name="plus"/>إضافة مؤسسة</a>@endif@endsection
@section('content')
<form class="filter-bar" method="get"><label class="search-field"><x-icon name="search"/><input name="q" value="{{ request('q') }}" placeholder="ابحث بالاسم أو رمز المؤسسة" aria-label="البحث عن مؤسسة" maxlength="100"></label>
<select name="kind" aria-label="نوع المؤسسة"><option value="">كل الأنواع</option>@foreach(\App\Support\Catalog::KINDS as $k=>$v)<option value="{{ $k }}" @selected(request('kind')===$k)>{{ $v }}</option>@endforeach</select>
<select name="governorate" aria-label="المحافظة"><option value="">كل المحافظات</option>@foreach(\App\Support\Catalog::GOVERNORATES as $g)<option @selected(request('governorate')===$g)>{{ $g }}</option>@endforeach</select><button class="btn btn-light">تصفية</button><a class="text-link" href="{{ route('facilities.index') }}">إعادة ضبط</a></form>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>المؤسسة</th><th>النوع / الملكية</th><th>المحافظة</th><th>التبعية الإدارية</th><th>الحالة</th><th>إجراء</th></tr></thead><tbody>
@forelse($facilities as $f)<tr><td><strong>{{ $f->name }}</strong><small class="mono">{{ $f->code }}</small></td><td>{{ \App\Support\Catalog::label($f->kind) }}<small>{{ \App\Support\Catalog::label($f->ownership) }}</small></td><td>{{ $f->governorate }}<small>{{ $f->city }}</small></td><td>{{ $f->parent?->name ?? 'مؤسسة مستقلة' }}</td><td><x-status :value="$f->status"/></td><td>@if(auth()->user()->allowed('facilities.manage'))<a class="btn btn-light btn-sm" href="{{ route('facilities.edit',$f) }}">إدارة المؤسسة</a>@else<span class="muted">عرض الدليل فقط</span>@endif</td></tr>@empty<tr><td colspan="6"><div class="empty-state"><x-icon name="building"/><strong>لا توجد مؤسسات مطابقة</strong><p>أضف مؤسسة جديدة أو غيّر معايير البحث.</p></div></td></tr>@endforelse
</tbody></table></div><x-pager :items="$facilities"/></div>
@endsection
