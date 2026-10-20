@extends('layout')
@section('title','طلب دم جديد')
@section('subtitle','اختر المصرف المرتبط وسجّل الاحتياج. فصيلة الطلب ليست نتيجة مخبرية معتمدة.')
@section('content')
<div class="alert alert-warning">للحالات الطارئة: اتبع بروتوكول التواصل الطارئ في المستشفى ولا تعتمد على الطلب الإلكتروني وحده.</div>
@if($links->isEmpty())<div class="panel empty-state"><x-icon name="network"/><strong>لا يوجد مصرف مرتبط وفعال</strong><p>أنشئ طلب ربط واعتمده أولاً من شاشة ربط المستشفيات.</p><a class="btn btn-primary" href="{{ route('links.index') }}">إدارة الربط</a></div>
@else<form method="post" action="{{ route('requests.store') }}" class="panel form-panel">@csrf
<div class="form-grid"><x-select name="link_id" label="المصرف المستلم" :options="$links->mapWithKeys(fn($l)=>[$l->id=>$l->bank->name])->all()" required/>
<x-select name="request_type" label="نوع الطلب" :options="['stock'=>'تعزيز رصيد المستشفى','patient'=>'طلب مرتبط بمريض']" value="stock" required/>
<x-select name="component" label="المكون المطلوب" :options="\App\Support\Catalog::COMPONENTS" required/>
<x-select name="blood_group" label="الفصيلة المطلوبة — ليست إثبات توافق" :options="array_combine(\App\Support\Catalog::GROUPS,array_map(fn($v)=>\App\Support\Catalog::label($v),\App\Support\Catalog::GROUPS))" required/>
<x-field name="quantity" type="number" label="عدد الوحدات المطلوبة" value="1" min="1" max="50" required/>
<x-select name="priority" label="الأولوية" :options="['routine'=>'اعتيادي','urgent'=>'عاجل','emergency'=>'طارئ']" value="routine" required/>
<x-field name="needed_at" type="datetime-local" label="موعد الاحتياج — توقيت بغداد" required :value="now()->timezone('Asia/Baghdad')->addHour()->format('Y-m-d\TH:i')"/>
<x-field name="patient_reference" label="مرجع المريض الداخلي — عند اختيار طلب مريض" maxlength="100" hint="مرجع داخلي فقط؛ لا تكتب الاسم أو التشخيص. لا يظهر لحساب الإدارة الوطنية."/>
</div><div class="form-actions"><button class="btn btn-primary">إرسال الطلب للمصرف</button><a class="btn btn-light" href="{{ route('requests.index') }}">رجوع</a></div></form>@endif@endsection
