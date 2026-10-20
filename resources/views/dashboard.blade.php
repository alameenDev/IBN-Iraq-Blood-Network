@extends('layout')
@section('title','لوحة المتابعة')
@section('subtitle','نظرة واحدة على الجهات المرتبطة والطلبات التي تحتاج إلى متابعة.')
@section('eyebrow','OVERVIEW / نظرة عامة')
@section('actions')
@if(auth()->user()->allowed('facilities.manage'))<a class="btn btn-primary" href="{{ route('facilities.create') }}"><x-icon name="plus"/>إضافة مؤسسة</a>
@elseif(auth()->user()->allowed('requests.create'))<a class="btn btn-primary" href="{{ route('requests.create') }}"><x-icon name="plus"/>طلب دم جديد</a>
@elseif(auth()->user()->allowed('donors.manage'))<a class="btn btn-primary" href="{{ route('donors.create') }}"><x-icon name="plus"/>تسجيل متبرع</a>@endif
@endsection
@section('content')
<section class="welcome-banner"><div><span class="banner-tag"><span class="status-dot"></span>مساحة عمل مترابطة</span><h2>تنسيق أفضل، من أول طلب.</h2><p>أنشئ الجهات، اعتمد ارتباطاتها، وتابع كل طلب ضمن صلاحيات مؤسستك.</p><a href="{{ route('workflow') }}">استعرض دورة العمل وحدود النسخة <x-icon name="arrow"/></a></div><div class="banner-visual" aria-hidden="true"><div class="banner-ring"></div><x-icon name="drop"/><span class="visual-chip"><x-icon name="network"/>IBN NETWORK</span></div></section>
<div class="stats-grid">@foreach($stats as $i=>$stat)<article class="stat-card"><div class="stat-top"><span>{{ $stat['label'] }}</span><div class="stat-icon tone-{{ $i }}"><x-icon :name="$stat['icon']"/></div></div><strong class="stat-value">{{ number_format($stat['value']) }}</strong><small>{{ $stat['hint'] }}</small></article>@endforeach</div>
<div class="dashboard-columns"><section class="panel"><div class="panel-head"><div><h2>أحدث طلبات الدم</h2><p>آخر الطلبات ضمن نطاق حسابك</p></div>@if(auth()->user()->allowed('requests.view'))<a class="text-link" href="{{ route('requests.index') }}">كل الطلبات ←</a>@endif</div>
<div class="table-wrap"><table><thead><tr><th>الطلب / المؤسسة</th><th>المكون</th><th>الأولوية</th><th>الحالة</th></tr></thead><tbody>
@forelse($recentRequests as $r)<tr><td><a class="table-title" href="{{ route('requests.show',$r) }}">{{ $r->hospital->name }}</a><small class="mono">{{ $r->reference }}</small></td><td><strong dir="ltr">{{ $r->component }} · {{ $r->blood_group==='unknown'?'?':$r->blood_group }}</strong><small>{{ $r->quantity }} وحدة مطلوبة</small></td><td><x-status :value="$r->priority"/></td><td><x-status :value="$r->status"/></td></tr>@empty<tr><td colspan="4"><div class="empty-state"><x-icon name="requests"/><strong>لا توجد طلبات للعرض</strong><p>ستظهر الطلبات الفعلية هنا بعد إنشاء الربط واعتماده.</p></div></td></tr>@endforelse
</tbody></table></div></section>
<section class="panel network-panel"><div class="panel-head"><div><h2>دليل الشبكة</h2><p>{{ auth()->user()->national()?'أحدث الجهات المسجلة':'مؤسستك الحالية' }}</p></div><x-icon name="network"/></div>
@forelse($recentFacilities as $f)<div class="facility-mini"><span class="facility-mini-icon"><x-icon name="building"/></span><div><strong>{{ $f->name }}</strong><small>{{ $f->governorate }} · {{ \App\Support\Catalog::label($f->kind) }}</small></div><x-status :value="$f->status"/></div>@empty<div class="empty-state"><strong>ابدأ بإضافة أول مؤسسة</strong><p>لا توجد بيانات افتراضية ضمن المخزون أو الشبكة.</p></div>@endforelse
@if(auth()->user()->allowed('facilities.view'))<a class="btn btn-light btn-full" href="{{ route('facilities.index') }}">عرض دليل الجهات <x-icon name="arrow"/></a>@endif
</section></div>
<section class="info-strip"><span class="info-icon"><x-icon name="shield"/></span><div><strong>القبول الإداري ليس موافقة طبية.</strong><p>المطابقة والفحوصات والمخزون والصرف الطبي مراحل لم تُفعّل في هذا الإصدار. الأرقام هنا من السجلات الإدارية فقط.</p></div><a href="{{ route('workflow') }}" class="text-link">تفاصيل النطاق ←</a></section>
@endsection
