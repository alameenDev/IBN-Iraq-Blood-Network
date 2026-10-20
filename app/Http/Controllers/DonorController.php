<?php
namespace App\Http\Controllers;
use App\Models\{Donor,DonationVisit};
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\{Rule,ValidationException};
class DonorController {
 public function index(Request $r) {
  $u=Access::require('donors.view'); $q=Access::donors($u);
  $d=$r->validate(['q'=>'nullable|string|max:100','identity_type'=>['nullable',Rule::in(['national_id','passport','other'])]]);
  if(!empty($d['q'])) {
   $term=trim($d['q']); $hash=Access::identityHash($d['identity_type']??'national_id',$term);
   $q->where(fn($q)=>$q->where('reference',$term)->orWhere('identity_hash',$hash));
  }
  return view('donors.index',['donors'=>$q->latest()->paginate(15)->withQueryString()]);
 }
 public function create() { $u=Access::require('donors.manage'); Access::donors($u); return view('donors.form'); }
 public function store(Request $r) {
  $u=Access::require('donors.manage'); Access::donors($u);
  $d=$r->validate(['full_name'=>'required|string|min:3|max:180','identity_type'=>['required',Rule::in(['national_id','passport','other'])],
   'identity_number'=>'required|string|min:4|max:50','phone'=>['nullable','regex:/^[+0-9 ()-]{7,25}$/'],
   'birth_date'=>'required|date_format:Y-m-d|before:today|after:'.now()->subYears(120)->format('Y-m-d'),
   'sex'=>['required',Rule::in(['male','female','unspecified'])],'consent'=>'accepted','consent_version'=>'required|string|max:60']);
  $hash=Access::identityHash($d['identity_type'],$d['identity_number']);
  if(Access::donors($u)->where('identity_hash',$hash)->exists()) throw ValidationException::withMessages(['identity_number'=>'المتبرع مسجل في مؤسستك. ابحث عنه وافتح زيارة جديدة.']);
  try {
   $donor=DB::transaction(function() use($u,$d,$hash) {
    $donor=Donor::create(['reference'=>'DON-'.Str::ulid(),'facility_id'=>$u->facility_id,'full_name'=>$d['full_name'],'identity_type'=>$d['identity_type'],
     'identity_number'=>$d['identity_number'],'identity_hash'=>$hash,'phone'=>$d['phone']??null,'birth_date'=>$d['birth_date'],'sex'=>$d['sex']]);
    Access::log('donor.registered','donor',$donor->id); $this->newVisit($donor,$d['consent_version'],$u->id); return $donor;
   });
  } catch(\Illuminate\Database\UniqueConstraintViolationException $e) { throw ValidationException::withMessages(['identity_number'=>'السجل أو الزيارة موجودان مسبقاً. حدّث الصفحة.']); }
  return redirect()->route('donors.show',$donor)->with('success','تم التسجيل وفتح زيارة بانتظار التقييم الطبي. لا يُسمح بالسحب من هذه الشاشة.');
 }
 public function show(int $donor) {
  $u=Access::require('donors.view'); $record=Access::donors($u)->with('visits')->findOrFail($donor);
  Access::log('donor.viewed','donor',$record->id); return view('donors.show',compact('record'));
 }
 public function visit(Request $r,int $donor) {
  $u=Access::require('donors.manage'); $d=$r->validate(['consent'=>'accepted','consent_version'=>'required|string|max:60']);
  DB::transaction(function() use($u,$donor,$d) {
   $record=Access::donors($u)->lockForUpdate()->findOrFail($donor);
   if($record->visits()->whereNotNull('active_donor_id')->exists()) throw ValidationException::withMessages(['consent'=>'توجد زيارة مفتوحة لهذا المتبرع.']);
   $this->newVisit($record,$d['consent_version'],$u->id);
  }); return back()->with('success','تم فتح زيارة جديدة بانتظار التقييم.');
 }
 private function newVisit(Donor $donor,string $version,int $userId): void {
  $v=DonationVisit::create(['reference'=>'VIS-'.Str::ulid(),'donor_id'=>$donor->id,'facility_id'=>$donor->facility_id,'active_donor_id'=>$donor->id,
   'status'=>'awaiting_screening','consent_version'=>$version,'consented_at'=>now(),'registered_by'=>$userId]);
  Access::log('visit.created','donation_visit',$v->id,['status'=>'awaiting_screening'],$donor->facility_id);
 }
 public function cancelVisit(Request $r,int $visit) {
  $u=Access::require('donors.manage'); Access::donors($u); $d=$r->validate(['reason'=>'required|string|min:3|max:500']);
  DB::transaction(function() use($u,$visit,$d) {
   $v=DonationVisit::where('facility_id',$u->facility_id)->lockForUpdate()->findOrFail($visit);
   abort_unless($v->status==='awaiting_screening',409,'الزيارة مغلقة.');
   $v->update(['status'=>'cancelled','active_donor_id'=>null,'cancellation_reason'=>$d['reason'],'cancelled_by'=>$u->id]);
   Access::log('visit.cancelled','donation_visit',$v->id,['status'=>'cancelled']);
  }); return back()->with('success','أُغلقت الزيارة دون حذف سجلها.');
 }
}
