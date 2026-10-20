<?php
namespace App\Http\Controllers;
use App\Models\{BloodRequest,FacilityLink};
use App\Support\{Access,Catalog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\{Rule,ValidationException};
use Carbon\CarbonImmutable;
class RequestController {
 public function index(Request $r) {
  $u=Access::require('requests.view'); $d=$r->validate(['status'=>['nullable',Rule::in(['submitted','accepted','rejected','cancelled'])],'q'=>'nullable|string|max:50']);
  $q=Access::requests($u)->with(['bank','hospital']);
  if(!empty($d['status'])) $q->where('status',$d['status']); if(!empty($d['q'])) $q->where('reference','like','%'.$d['q'].'%');
  return view('requests.index',['requests'=>$q->latest()->paginate(15)->withQueryString()]);
 }
 public function create() {
  $u=Access::require('requests.create'); $f=Access::facility($u); abort_unless($f->isHospital(),403);
  $links=FacilityLink::with('bank')->where('hospital_id',$f->id)->where('status','active')->whereHas('bank',fn($q)=>$q->where('status','active'))->get();
  return view('requests.form',compact('links'));
 }
 public function store(Request $r) {
  $u=Access::require('requests.create'); $f=Access::facility($u); abort_unless($f->isHospital(),403);
  $d=$r->validate(['link_id'=>'required|integer','request_type'=>['required',Rule::in(['stock','patient'])],
   'component'=>['required',Rule::in(array_keys(Catalog::COMPONENTS))],'blood_group'=>['required',Rule::in(Catalog::GROUPS)],
   'quantity'=>'required|integer|min:1|max:50','priority'=>['required',Rule::in(['routine','urgent','emergency'])],
   'needed_at'=>'required|date_format:Y-m-d\TH:i','patient_reference'=>'nullable|required_if:request_type,patient|string|max:100']);
  $needed=CarbonImmutable::createFromFormat('Y-m-d\TH:i',$d['needed_at'],'Asia/Baghdad')->utc();
  if($needed->lt(now()->subMinutes(5))||$needed->gt(now()->addYear())) throw ValidationException::withMessages(['needed_at'=>'حدد وقت احتياج حالي أو مستقبلي ضمن سنة.']);
  $record=DB::transaction(function() use($u,$f,$d,$needed) {
   $link=FacilityLink::where('hospital_id',$f->id)->lockForUpdate()->findOrFail($d['link_id']);
   abort_unless($link->status==='active'&&$link->bank->status==='active'&&$link->hospital->status==='active',422,'الربط غير فعال.');
   $record=BloodRequest::create(['reference'=>'REQ-'.Str::ulid(),'hospital_id'=>$f->id,'bank_id'=>$link->bank_id,'link_id'=>$link->id,
    'created_by'=>$u->id,'request_type'=>$d['request_type'],'component'=>$d['component'],'blood_group'=>$d['blood_group'],
    'quantity'=>$d['quantity'],'priority'=>$d['priority'],'needed_at'=>$needed,
    'patient_reference'=>$d['request_type']==='patient'?$d['patient_reference']:null,'status'=>'submitted']);
   foreach([$record->hospital_id,$record->bank_id] as $fid) Access::log('request.submitted','blood_request',$record->id,['component'=>$record->component,'quantity'=>$record->quantity],$fid);
   return $record;
  });
  return redirect()->route('requests.show',$record)->with('success','تم إرسال الطلب. هذا ليس حجزاً لوحدة ولا موافقة صرف طبية.');
 }
 public function show(int $request) {
  $u=Access::require('requests.view'); $record=Access::requests($u)->with(['bank','hospital'])->findOrFail($request);
  Access::log('request.viewed','blood_request',$record->id);
  return view('requests.show',compact('record'));
 }
 public function review(Request $r,int $request) {
  $u=Access::require('requests.review'); $f=Access::facility($u);
  $d=$r->validate(['status'=>['required',Rule::in(['accepted','rejected'])],'reason'=>'required|string|min:3|max:500']);
  DB::transaction(function() use($request,$u,$f,$d) {
   $record=BloodRequest::where('bank_id',$f->id)->lockForUpdate()->findOrFail($request);
   abort_unless($record->status==='submitted',409,'تمت معالجة الطلب مسبقاً.');
   $link=FacilityLink::lockForUpdate()->findOrFail($record->link_id);
   abort_unless($link->status==='active'&&$link->bank->status==='active'&&$link->hospital->status==='active',422,'لا يمكن اعتماد الطلب لأن الربط أو المؤسسة موقوفة.');
   $record->update(['status'=>$d['status'],'decision_reason'=>$d['reason'],'decided_by'=>$u->id,'decided_at'=>now()]);
   foreach([$record->hospital_id,$record->bank_id] as $fid) Access::log('request.reviewed','blood_request',$record->id,['before'=>'submitted','after'=>$d['status']],$fid);
  });
  return back()->with('success','تم تسجيل القرار الإداري. لا توجد عملية صرف أو مطابقة طبية في هذه المرحلة.');
 }
 public function cancel(Request $r,int $request) {
  $u=Access::require('requests.create'); $f=Access::facility($u); $d=$r->validate(['reason'=>'required|string|min:3|max:500']);
  DB::transaction(function() use($request,$u,$f,$d) {
   $record=BloodRequest::where('hospital_id',$f->id)->lockForUpdate()->findOrFail($request);
   abort_unless(in_array($record->status,['submitted','accepted'],true),409,'لا يمكن إلغاء هذه الحالة.'); $before=$record->status;
   $record->update(['status'=>'cancelled','decision_reason'=>$d['reason'],'decided_by'=>$u->id,'decided_at'=>now()]);
   foreach([$record->hospital_id,$record->bank_id] as $fid) Access::log('request.cancelled','blood_request',$record->id,['before'=>$before,'after'=>'cancelled'],$fid);
  });
  return back()->with('success','تم إلغاء الطلب مع الاحتفاظ بتاريخه.');
 }
}
