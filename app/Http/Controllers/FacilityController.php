<?php
namespace App\Http\Controllers;
use App\Models\Facility;
use App\Support\{Access,Catalog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class FacilityController {
 public function index(Request $r) {
  $u=Access::require('facilities.view'); $data=$r->validate(['q'=>'nullable|string|max:100','kind'=>['nullable',Rule::in(array_keys(Catalog::KINDS))],'status'=>['nullable',Rule::in(['pending','active','suspended'])],'governorate'=>['nullable',Rule::in(Catalog::GOVERNORATES)]]);
  $q=Facility::query()->when(!$u->national(),fn($q)=>$q->where('status','active'));
  if(!empty($data['q'])) $q->where(fn($q)=>$q->where('name','like','%'.$data['q'].'%')->orWhere('code','like','%'.$data['q'].'%'));
  foreach(['kind','status','governorate'] as $f) if(!empty($data[$f])) $q->where($f,$data[$f]);
  return view('facilities.index',['facilities'=>$q->with('parent')->latest()->paginate(15)->withQueryString()]);
 }
 public function create() { Access::require('facilities.manage'); return view('facilities.form',['facility'=>new Facility,'parents'=>Facility::where('status','active')->get()]); }
 public function edit(int $facility) { Access::require('facilities.manage'); return view('facilities.form',['facility'=>Facility::findOrFail($facility),'parents'=>Facility::where('id','!=',$facility)->where('status','active')->get()]); }
 public function store(Request $r) { return $this->save($r,new Facility); }
 public function update(Request $r,int $facility) { Access::require('facilities.manage'); return $this->save($r,Facility::findOrFail($facility)); }
 private function save(Request $r,Facility $f) {
  Access::require('facilities.manage');
  $data=$r->validate([
   'code'=>['required','regex:/^[A-Z0-9][A-Z0-9_-]{1,31}$/',Rule::unique('facilities')->ignore($f->id)],
   'name'=>'required|string|max:180','kind'=>['required',Rule::in(array_keys(Catalog::KINDS))],
   'ownership'=>['required',Rule::in(['government','private'])],'governorate'=>['required',Rule::in(Catalog::GOVERNORATES)],
   'city'=>'nullable|string|max:100','address'=>'nullable|string|max:240','phone'=>'nullable|string|max:32','email'=>'nullable|email|max:180',
   'license_number'=>'nullable|string|max:100','parent_id'=>'nullable|integer|exists:facilities,id',
   'status'=>['required',Rule::in(['pending','active','suspended'])],'collects_donations'=>'required|boolean',
  ]);
  if($f->exists) abort_unless($data['code']===$f->code && $data['kind']===$f->kind,422,'نوع المؤسسة ورمزها ثابتان بعد الإنشاء.');
  if($data['status']==='active' && empty($data['license_number'])) throw \Illuminate\Validation\ValidationException::withMessages(['license_number'=>'سجل مرجع الموافقة التنظيمية قبل التفعيل.']);
  if($data['collects_donations']) abort_unless(in_array($data['kind'],['main_bank','collection_center'],true),422,'الجمع في هذه النسخة متاح للمصارف الرئيسية ومراكز التبرع فقط.');
  if(!empty($data['parent_id'])) {
   abort_unless(in_array($data['kind'],['branch_bank','collection_center'],true),422,'الارتباط الإداري مخصص للفروع ومراكز التبرع.');
   $p=Facility::findOrFail($data['parent_id']); $allowed=$data['kind']==='collection_center'?['main_bank']:['hospital','main_bank'];
   abort_unless(in_array($p->kind,$allowed,true)&&$p->status==='active'&&$p->id!==$f->id,422,'المؤسسة الأم غير صالحة لهذا النوع.');
   $seen=[]; while($p) { abort_if($p->id===$f->id || isset($seen[$p->id]),422,'الربط الإداري الدائري غير مسموح.'); $seen[$p->id]=true; $p=$p->parent; }
  }
  DB::transaction(function() use($f,$data) {
   $before=$f->only(['status','parent_id','collects_donations']); $f->fill($data)->save();
   Access::log('facility.saved','facility',$f->id,['before'=>$before,'after'=>$f->only(['status','parent_id','collects_donations'])],$f->id);
   if($f->status!=='active') DB::table('sessions')->whereIn('user_id',\App\Models\User::where('facility_id',$f->id)->select('id'))->delete();
  });
  return redirect()->route('facilities.index')->with('success','تم حفظ المؤسسة. الربط والتجهيز يحتاجان موافقة مستقلة.');
 }
}
