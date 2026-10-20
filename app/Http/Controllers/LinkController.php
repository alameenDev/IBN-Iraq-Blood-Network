<?php
namespace App\Http\Controllers;
use App\Models\{Facility,FacilityLink};
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class LinkController {
 public function index() {
  $u=Access::require('links.view');
  return view('links.index',['links'=>Access::links($u)->with(['bank','hospital','requester'])->latest()->paginate(15),
   'banks'=>Facility::where('status','active')->where('kind','main_bank')->get(),
   'hospitals'=>Facility::where('status','active')->whereIn('kind',['hospital','branch_bank'])->get()]);
 }
 public function store(Request $r) {
  $u=Access::require('links.request'); $d=$r->validate(['bank_id'=>'required|integer|exists:facilities,id','hospital_id'=>'required|integer|exists:facilities,id','note'=>'required|string|min:3|max:500','origin'=>['nullable',Rule::in(['bank','hospital'])]]);
  DB::transaction(function() use($d,$u) {
   $facilities=Facility::whereIn('id',[$d['bank_id'],$d['hospital_id']])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
   $bank=$facilities[$d['bank_id']]; $hospital=$facilities[$d['hospital_id']];
   abort_unless($bank->kind==='main_bank'&&$hospital->isHospital()&&$bank->status==='active'&&$hospital->status==='active',422,'حدد مصرفاً رئيسياً ومستشفى فعالين.');
   if(!$u->national()) abort_unless(in_array($u->facility_id,[$bank->id,$hospital->id],true),403);
   $origin=$u->national()?(($d['origin']??'hospital')==='bank'?$bank->id:$hospital->id):$u->facility_id;
   $link=FacilityLink::where('bank_id',$bank->id)->where('hospital_id',$hospital->id)->lockForUpdate()->first();
   if($link && in_array($link->status,['pending','active'],true)) throw ValidationException::withMessages(['bank_id'=>'يوجد رابط فعال أو طلب قيد المراجعة بين الجهتين.']);
   $link??=new FacilityLink; $link->fill(['bank_id'=>$bank->id,'hospital_id'=>$hospital->id,'requested_facility_id'=>$origin,'requested_by'=>$u->id,
    'status'=>'pending','note'=>$d['note'],'decision_reason'=>null,'reviewed_by'=>null,'reviewed_at'=>null])->save();
   Access::log('link.requested','facility_link',$link->id,['bank_id'=>$bank->id,'hospital_id'=>$hospital->id],$origin);
  });
  return back()->with('success','أُرسل طلب الربط. لن يُسمح بطلب الدم حتى الموافقة.');
 }
 public function review(Request $r,int $link) {
  $u=Access::require('links.review'); $d=$r->validate(['status'=>['required',Rule::in(['active','rejected','suspended'])],'reason'=>'required|string|min:3|max:500']);
  DB::transaction(function() use($u,$link,$d) {
   $l=Access::links($u)->lockForUpdate()->findOrFail($link); $before=$l->status;
   if($d['status']==='suspended') abort_unless($l->status==='active',409,'يمكن إيقاف الربط الفعال فقط.');
   else {
    abort_unless($l->status==='pending',409,'تمت معالجة طلب الربط مسبقاً.');
    abort_if($l->requested_by===$u->id,403,'منشئ الطلب لا يستطيع اعتماد طلبه.');
    abort_unless($u->national() || $u->facility_id!==$l->requested_facility_id,403,'الاعتماد للجهة المقابلة أو إدارة الشبكة.');
    abort_unless($l->bank->status==='active'&&$l->hospital->status==='active',422,'إحدى المؤسستين غير فعالة.');
   }
   $l->update(['status'=>$d['status'],'decision_reason'=>$d['reason'],'reviewed_by'=>$u->id,'reviewed_at'=>now()]);
   foreach([$l->bank_id,$l->hospital_id] as $facilityId) Access::log('link.reviewed','facility_link',$l->id,['before'=>$before,'after'=>$d['status']],$facilityId);
  });
  return back()->with('success','تم حفظ قرار الربط وتسجيله في سجل التدقيق.');
 }
}
