<!doctype html>
<html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>@yield('title','لوحة المتابعة') | IBN</title>
<link rel="stylesheet" href="{{ asset('assets/app.css') }}"><script src="{{ asset('assets/app.js') }}" defer></script></head>
<body>
@php($me=auth()->user())
<a href="#main" class="skip-link">انتقل إلى المحتوى</a>
<div class="sidebar-overlay" data-menu-close></div>
<aside class="sidebar" id="sidebar" aria-label="القائمة الرئيسية">
 <a href="{{ route('dashboard') }}" class="brand"><span class="brand-mark"><x-icon name="drop"/></span><span><strong>IBN<span class="brand-dot">.</span></strong><small>IRAQ BLOOD NETWORK</small></span></a>
 <div class="workspace"><span class="workspace-icon"><x-icon name="building"/></span><div><small>مساحة العمل</small><strong>{{ $me->national()?'إدارة الشبكة الوطنية':$me->facility->name }}</strong></div></div>
 <nav class="nav-list">
 <span class="nav-label">مساحة العمل</span>
 @php($nav=[['dashboard','dashboard.view','grid','لوحة المتابعة'],['facilities.index','facilities.view','building','المؤسسات والمستشفيات'],['links.index','links.view','network','ربط المستشفيات'],['requests.index','requests.view','requests','طلبات الدم'],['donors.index','donors.view','drop','المتبرعون والزيارات']])
 @foreach($nav as [$route,$permission,$icon,$text])
 @if($me->allowed($permission) && ($route!=='donors.index'||$me->facility?->collects_donations))
 <a href="{{ route($route) }}" class="nav-item {{ request()->routeIs(explode('.',$route)[0].'*')?'selected':'' }}"><x-icon :name="$icon"/><span>{{ $text }}</span></a>
 @endif @endforeach
 <span class="nav-label">الإدارة والرقابة</span>
 @foreach([['users.index','users.view','users','المستخدمون'],['roles.index','roles.view','shield','الأدوار والصلاحيات'],['audit.index','audit.view','clock','سجل التدقيق']] as [$route,$permission,$icon,$text])
 @if($me->allowed($permission))<a href="{{ route($route) }}" class="nav-item {{ request()->routeIs(explode('.',$route)[0].'*')?'selected':'' }}"><x-icon :name="$icon"/><span>{{ $text }}</span></a>@endif
 @endforeach
 <a href="{{ route('workflow') }}" class="nav-item {{ request()->routeIs('workflow')?'selected':'' }}"><x-icon name="pulse"/><span>دورة الدم وخطة التطوير</span></a>
 </nav>
 <div class="sidebar-bottom"><div class="pilot-tag">النسخة التجريبية <b dir="ltr">0.1</b></div><p>لا تستخدم للقرارات الطبية أو صرف الدم.</p><a href="{{ route('password.edit') }}" class="nav-item"><x-icon name="lock"/>حسابي وكلمة المرور</a></div>
</aside>
<div class="app-shell">
 <header class="topbar"><div class="topbar-start"><button class="icon-button menu-toggle" type="button" data-menu-toggle aria-controls="sidebar" aria-expanded="false" aria-label="فتح القائمة"><x-icon name="menu"/></button><div class="breadcrumb">شبكة مصارف الدم <span>/</span> <b>@yield('title','لوحة المتابعة')</b></div></div>
 <div class="topbar-end"><span class="scope-pill"><x-icon name="shield"/>{{ $me->role->name }}</span><span class="avatar">{{ mb_substr($me->name,0,1) }}</span><div class="user-info"><strong>{{ $me->name }}</strong><small>{{ $me->national()?'إدارة وطنية':'حساب مؤسسة' }}</small></div><form action="{{ route('logout') }}" method="post">@csrf<button class="icon-button" aria-label="تسجيل الخروج" title="تسجيل الخروج"><x-icon name="logout"/></button></form></div></header>
 <main id="main" class="main">
 @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="alert alert-error" role="alert"><strong>راجع البيانات التالية:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <div class="page-heading"><div><div class="eyebrow">@yield('eyebrow','IRAQ BLOOD NETWORK')</div><h1>@yield('title','لوحة المتابعة')</h1><p>@yield('subtitle','إدارة واضحة، صلاحيات محددة، وتتبع لكل إجراء.')</p></div><div class="page-actions">@yield('actions')</div></div>
 @yield('content')
 <footer class="page-footer"><span>IBN · الشبكة العراقية لمصارف الدم</span><span>بيئة تجريبية · الأوقات المعروضة بتوقيت بغداد</span></footer>
 </main>
</div></body></html>
