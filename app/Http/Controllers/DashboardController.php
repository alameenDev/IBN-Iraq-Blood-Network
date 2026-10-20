<?php
namespace App\Http\Controllers;
use App\Models\{Facility,DonationVisit};
use App\Support\Access;
class DashboardController {
 public function index() {
  $u=Access::require('dashboard.view'); $requests=Access::requests($u);
  $stats=[
   ['label'=>$u->national()?'المؤسسات الفعالة':'روابط المؤسسة الفعالة','value'=>$u->national()?Facility::where('status','active')->count():Access::links($u)->where('status','active')->count(),'hint'=>'جهات مسجلة في النظام','icon'=>'building'],
   ['label'=>'طلبات بانتظار المراجعة','value'=>(clone $requests)->where('status','submitted')->count(),'hint'=>'لم تُقبل إدارياً بعد','icon'=>'requests'],
   ['label'=>'طلبات عاجلة مفتوحة','value'=>(clone $requests)->whereIn('status',['submitted','accepted'])->whereIn('priority',['urgent','emergency'])->count(),'hint'=>'الأولوية لا تعني اعتماداً طبياً','icon'=>'pulse'],
   ['label'=>'روابط بانتظار الموافقة','value'=>Access::links($u)->where('status','pending')->count(),'hint'=>'تحتاج مراجعة الجهة المخولة','icon'=>'network'],
  ];
  $recentRequests=$u->allowed('requests.view')?(clone $requests)->with(['bank','hospital'])->latest()->limit(6)->get():collect();
  $recentFacilities=$u->national()?Facility::latest()->limit(5)->get():collect([$u->facility]);
  return view('dashboard',compact('stats','recentRequests','recentFacilities'));
 }
 public function workflow() { Access::require('dashboard.view'); return view('workflow'); }
}
