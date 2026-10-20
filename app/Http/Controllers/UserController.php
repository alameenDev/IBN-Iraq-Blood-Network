<?php
namespace App\Http\Controllers;
use App\Models\{User,Role,Facility};
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Hash};
use Illuminate\Validation\{Rule,ValidationException};
use Illuminate\Validation\Rules\Password;
class UserController {
 public function index() { $u=Access::require('users.view'); return view('users.index',['users'=>Access::users($u)->with(['role','facility'])->latest()->paginate(15)]); }
 public function create() { $u=Access::require('users.manage'); return $this->form($u,new User); }
 public function edit(int $user) { $u=Access::require('users.manage'); return $this->form($u,Access::users($u)->findOrFail($user)); }
 private function form(User $actor,User $user) {
  $roles=Role::with('permissions')->get()->filter(fn($r)=>$actor->national() || ($r->scope==='facility' && $r->permissions->every(fn($p)=>$actor->allowed($p->slug))));
  return view('users.form',['account'=>$user,'roles'=>$roles,'facilities'=>Facility::where('status','active')->get()]);
 }
 public function store(Request $r) { $u=Access::require('users.manage'); return $this->save($r,$u,new User); }
 public function update(Request $r,int $user) { $u=Access::require('users.manage'); return $this->save($r,$u,Access::users($u)->findOrFail($user)); }
 private function save(Request $r,User $actor,User $user) {
  $r->merge(['email'=>mb_strtolower(trim((string)$r->input('email')))]);
  $d=$r->validate(['name'=>'required|string|max:120','email'=>['required','email','max:180',Rule::unique('users')->ignore($user->id)],
   'role_id'=>'required|integer|exists:roles,id','facility_id'=>'nullable|integer|exists:facilities,id','active'=>'required|boolean',
   'password'=>[$user->exists?'nullable':'required','confirmed','max:200',Password::min(12)->mixedCase()->numbers()->symbols()]]);
  $role=Role::with('permissions')->findOrFail($d['role_id']);
  if(!$actor->national()) abort_unless($role->scope==='facility' && $role->permissions->every(fn($p)=>$actor->allowed($p->slug)),403,'لا يمكنك منح صلاحيات لا تملكها.');
  $facilityId=$role->scope==='national'?null:($actor->national()?($d['facility_id']??null):$actor->facility_id);
  if($role->scope==='facility') abort_unless($facilityId && Facility::whereKey($facilityId)->where('status','active')->exists(),422,'اختر مؤسسة فعالة للحساب.');
  if(!$actor->national() && isset($d['facility_id'])) abort_unless((int)$d['facility_id']===$actor->facility_id,403);
  if($user->id===$actor->id) {
   abort_unless($role->id===$user->role_id && (bool)$d['active'] && $facilityId===$user->facility_id && empty($d['password']),422,'لا يمكنك تعطيل نفسك أو تعديل دورك. غيّر كلمة المرور من حسابي.');
  }
  DB::transaction(function() use($d,$user,$role,$facilityId) {
   // Serialize administrator changes, protecting the last active network administrator.
   $adminRole=Role::where('slug','platform_admin')->firstOrFail();
   $admins=User::where('role_id',$adminRole->id)->where('active',true)->orderBy('id')->lockForUpdate()->get();
   if($user->exists && $user->role_id===$adminRole->id && $user->active && (!$d['active'] || $role->id!==$adminRole->id) && $admins->count()<=1) {
    throw ValidationException::withMessages(['active'=>'يجب الإبقاء على مدير شبكة فعال واحد على الأقل.']);
   }
   $before=$user->only(['role_id','facility_id','active']);
   $values=['name'=>$d['name'],'email'=>mb_strtolower($d['email']),'role_id'=>$role->id,'facility_id'=>$facilityId,'active'=>(bool)$d['active']];
   if(!empty($d['password'])) $values+=['password'=>Hash::make($d['password']),'must_change_password'=>true,'remember_token'=>null];
   $user->fill($values)->save(); DB::table('sessions')->where('user_id',$user->id)->delete();
   Access::log('user.saved','user',$user->id,['before'=>$before,'after'=>$user->only(['role_id','facility_id','active']),'password_reset'=>!empty($d['password'])],$facilityId);
  });
  return redirect()->route('users.index')->with('success','تم حفظ الحساب. الحساب الجديد أو المعاد ضبطه ملزم بتغيير كلمة المرور.');
 }
}
