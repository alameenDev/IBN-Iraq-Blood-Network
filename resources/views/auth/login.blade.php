<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>تسجيل الدخول | IBN</title><link rel="stylesheet" href="{{ asset('assets/app.css') }}"></head>
<body class="login-body"><main class="login-card">
<section class="login-brand"><div class="brand"><span class="brand-mark"><x-icon name="drop"/></span><span><strong>IBN.</strong><small>IRAQ BLOOD NETWORK</small></span></div><div class="login-hero"><span class="eyebrow">كل ارتباط يصنع فرقاً</span><h1>شبكة واحدة.<br>رعاية أكثر ترابطاً.</h1><p>من مصرف الدم إلى المستشفى؛ مساحة عمل موحدة للجهات والطلبات والصلاحيات.</p><div class="login-art" aria-hidden="true"><div class="orbit orbit-1"></div><div class="orbit orbit-2"></div><div class="drop-emblem"><x-icon name="drop"/></div><span class="orbit-node node-1"><x-icon name="building"/></span><span class="orbit-node node-2"><x-icon name="network"/></span><span class="orbit-node node-3"><x-icon name="shield"/></span></div></div><small>نسخة تجريبية إدارية — ليست نظاماً معتمداً للصرف الطبي.</small></section>
<section class="login-form"><span class="pill">مرحباً بك في IBN</span><h2>تسجيل الدخول</h2><p>استخدم الحساب الذي أنشأه مسؤول مؤسستك.</p>
@if($errors->any())<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('login') }}">@csrf
<x-field name="email" type="email" label="البريد الإلكتروني" required autocomplete="username" dir="ltr" maxlength="180"/>
<x-field name="password" type="password" label="كلمة المرور" required autocomplete="current-password" dir="ltr" maxlength="200"/>
<button class="btn btn-primary btn-full">دخول إلى مساحة العمل <x-icon name="arrow"/></button>
</form><div class="login-help"><x-icon name="lock"/><p>الحسابات لا تُنشأ ذاتياً. لاستعادة الوصول، تواصل مع مدير مؤسستك أو مسؤول الشبكة.</p></div><small class="muted">لا تُدخل بيانات متبرعين أو مرضى حقيقية في هذه النسخة التجريبية.</small></section>
</main></body></html>
