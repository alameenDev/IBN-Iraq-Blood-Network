<?php
namespace App\Support;
final class Catalog {
 public const KINDS=['main_bank'=>'مصرف دم رئيسي','branch_bank'=>'مصرف دم فرعي','hospital'=>'مستشفى','collection_center'=>'مركز تبرع'];
 public const GOVERNORATES=['بغداد','البصرة','نينوى','أربيل','النجف','كربلاء','واسط','ميسان','ذي قار','القادسية','المثنى','بابل','ديالى','الأنبار','صلاح الدين','كركوك','دهوك','السليمانية','حلبجة'];
 public const COMPONENTS=['RBC'=>'كريات الدم الحمراء','FFP'=>'بلازما مجمدة','PLT'=>'صفائح دموية','CRYO'=>'راسب بارد','WB'=>'دم كامل'];
 public const GROUPS=['A+','A-','B+','B-','AB+','AB-','O+','O-','unknown'];
 public const PERMISSIONS=[
  'dashboard.view'=>['لوحة المتابعة',true], 'facilities.view'=>['دليل الجهات',true], 'facilities.manage'=>['إنشاء وتفعيل الجهات',false],
  'links.view'=>['عرض ربط المستشفيات',true], 'links.request'=>['طلب ربط مؤسسة',true], 'links.review'=>['مراجعة الربط',true],
  'users.view'=>['عرض المستخدمين',true], 'users.manage'=>['إدارة مستخدمي المؤسسة',true],
  'roles.view'=>['عرض الأدوار والصلاحيات',true], 'roles.manage'=>['إدارة الأدوار المخصصة',false],
  'requests.view'=>['عرض طلبات الدم',true], 'requests.create'=>['إنشاء طلب دم',true], 'requests.review'=>['مراجعة طلبات التجهيز',true],
  'donors.view'=>['عرض سجلات متبرعي المؤسسة',true], 'donors.manage'=>['تسجيل المتبرعين والزيارات',true], 'audit.view'=>['سجل التدقيق',true],
 ];
 public const ROLES=[
  'platform_admin'=>['مدير الشبكة الوطنية','national',['dashboard.view','facilities.view','facilities.manage','links.view','links.request','links.review','users.view','users.manage','roles.view','roles.manage','requests.view','audit.view']],
  'bank_manager'=>['مدير مصرف الدم','facility',['dashboard.view','facilities.view','links.view','links.request','links.review','users.view','users.manage','roles.view','requests.view','requests.review','donors.view','donors.manage','audit.view']],
  'hospital_manager'=>['مدير المستشفى','facility',['dashboard.view','facilities.view','links.view','links.request','links.review','users.view','users.manage','roles.view','requests.view','requests.create','audit.view']],
  'hospital_staff'=>['موظف طلبات المستشفى','facility',['dashboard.view','facilities.view','links.view','requests.view','requests.create']],
  'donor_registrar'=>['موظف تسجيل المتبرعين','facility',['dashboard.view','donors.view','donors.manage']],
  'bank_coordinator'=>['منسق طلبات المصرف','facility',['dashboard.view','links.view','requests.view','requests.review']],
  'auditor'=>['مدقق المؤسسة','facility',['dashboard.view','audit.view','roles.view']],
 ];
 public static function label(?string $value): string {
  return (self::KINDS + self::COMPONENTS + [
   'pending'=>'بانتظار الموافقة','active'=>'فعال','suspended'=>'موقوف','rejected'=>'مرفوض',
   'submitted'=>'طلب جديد','accepted'=>'مقبول إدارياً — لم يُصرف','cancelled'=>'ملغى',
   'awaiting_screening'=>'بانتظار التقييم الطبي','routine'=>'اعتيادي','urgent'=>'عاجل','emergency'=>'طارئ',
   'government'=>'حكومي','private'=>'أهلي','stock'=>'تعزيز رصيد','patient'=>'طلب مرتبط بمريض','unknown'=>'غير مؤكد',
   'national'=>'وطني','facility'=>'المؤسسة فقط','national_id'=>'بطاقة وطنية','passport'=>'جواز سفر','other'=>'هوية أخرى',
   'male'=>'ذكر','female'=>'أنثى','unspecified'=>'غير محدد',
  ])[$value ?? ''] ?? ($value ?? '—');
 }
}
