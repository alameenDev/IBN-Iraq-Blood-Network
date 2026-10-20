@extends('layout')
@section('title','تسجيل متبرع جديد')
@section('subtitle','تسجيل بيانات التعريف والموافقة وفتح زيارة؛ لا يُعد قرار أهلية للتبرع.')
@section('content')<div class="alert alert-warning">بيئة تجريبية: استخدم بيانات مصطنعة فقط. لا توجد في هذه النسخة صلاحية لسحب الدم أو الإفراج عن مكوناته.</div>
<form class="panel form-panel" method="post" action="{{ route('donors.store') }}">@csrf<div class="form-grid">
<x-field name="full_name" label="الاسم الكامل" required maxlength="180"/>
<x-select name="identity_type" label="نوع الهوية" :options="['national_id'=>'بطاقة وطنية','passport'=>'جواز سفر','other'=>'هوية أخرى']" value="national_id" required/>
<x-field name="identity_number" label="رقم الهوية" required maxlength="50" dir="ltr"/><x-field name="birth_date" label="تاريخ الميلاد" type="date" required/>
<x-field name="phone" label="رقم الهاتف" type="tel" maxlength="25" dir="ltr"/><x-select name="sex" label="الجنس المسجل" :options="['male'=>'ذكر','female'=>'أنثى','unspecified'=>'غير محدد']" required/>
<x-field name="consent_version" label="مرجع / إصدار نموذج الموافقة الموثق" required maxlength="60" hint="يشير إلى النموذج المعتمد لدى المؤسسة؛ النظام لا يعتمد النص الطبي نيابة عنها."/>
</div><label class="check-row"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>تم التحقق من هوية المتبرع وتوثيق موافقة التسجيل حسب نموذج المؤسسة.</span></label>
<div class="form-actions"><button class="btn btn-primary">حفظ المتبرع وفتح زيارة</button><a class="btn btn-light" href="{{ route('donors.index') }}">رجوع</a></div></form>@endsection
