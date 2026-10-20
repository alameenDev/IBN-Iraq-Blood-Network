@extends('layout')
@section('title',$facility->exists?'إدارة المؤسسة':'إضافة مؤسسة')
@section('subtitle','التبعية الإدارية لا تمنح صلاحية مشاركة البيانات أو طلب الدم تلقائياً.')
@section('content')
<form action="{{ $facility->exists?route('facilities.update',$facility):route('facilities.store') }}" method="post" class="panel form-panel">@csrf @if($facility->exists)@method('put')@endif
<div class="section-heading"><span>01</span><div><h2>هوية المؤسسة</h2><p>الاسم الرسمي ونوع الجهة داخل الشبكة.</p></div></div>
<div class="form-grid"><x-field name="name" label="اسم المؤسسة" :value="$facility->name" required maxlength="180"/>
<x-field name="code" label="رمز المؤسسة" :value="$facility->code" required maxlength="32" dir="ltr" :readonly="$facility->exists" hint="حروف إنجليزية كبيرة وأرقام؛ رمز فريد لا يتغير بعد الإنشاء."/>
@if($facility->exists)<input type="hidden" name="kind" value="{{ $facility->kind }}"><div class="field"><span>نوع المؤسسة</span><div class="readonly-value">{{ \App\Support\Catalog::label($facility->kind) }}</div></div>@else<x-select name="kind" label="نوع المؤسسة" :options="\App\Support\Catalog::KINDS" required/>@endif
<x-select name="ownership" label="الملكية" :options="['government'=>'حكومي','private'=>'أهلي']" :value="$facility->ownership??'government'" required/>
<x-select name="governorate" label="المحافظة" :options="array_combine(\App\Support\Catalog::GOVERNORATES,\App\Support\Catalog::GOVERNORATES)" :value="$facility->governorate" required/>
<x-field name="city" label="المدينة / القضاء" :value="$facility->city" maxlength="100"/>
<x-select name="parent_id" label="المؤسسة الأم — للفروع ومراكز التبرع" :options="$parents->pluck('name','id')->all()" :value="$facility->parent_id" placeholder="بدون تبعية إدارية"/>
<x-field name="license_number" label="مرجع الترخيص أو الموافقة التنظيمية" :value="$facility->license_number" maxlength="100" hint="إلزامي للتفعيل الإداري. لا يتحقق النظام من صحة الموافقات الخارجية تلقائياً."/>
</div>
<div class="section-heading"><span>02</span><div><h2>التواصل والتفعيل</h2><p>لا تنشئ مؤسسات حقيقية قبل استكمال الاتفاقيات والمراجعة التنظيمية.</p></div></div>
<div class="form-grid"><x-field name="phone" label="رقم تواصل المؤسسة" :value="$facility->phone" dir="ltr" maxlength="32"/><x-field name="email" label="البريد الرسمي" type="email" :value="$facility->email" dir="ltr" maxlength="180"/><x-field name="address" label="العنوان" :value="$facility->address" maxlength="240"/>
<x-select name="status" label="حالة المؤسسة" :options="['pending'=>'بانتظار الموافقة','active'=>'فعال','suspended'=>'موقوف']" :value="$facility->status??'pending'" required/>
</div>
<input type="hidden" name="collects_donations" value="0"><label class="check-row"><input type="checkbox" name="collects_donations" value="1" @checked(old('collects_donations',$facility->collects_donations))><span><strong>إتاحة تسجيل المتبرعين والزيارات</strong><small>للمصرف الرئيسي أو مركز التبرع فقط. لا يفعّل السحب أو الإفراج الطبي.</small></span></label>
<div class="form-actions"><button class="btn btn-primary">حفظ المؤسسة</button><a class="btn btn-light" href="{{ route('facilities.index') }}">رجوع</a></div></form>
@endsection
