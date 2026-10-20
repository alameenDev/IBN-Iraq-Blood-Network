<?php
namespace App\Http\Controllers;
use App\Models\{Role,Permission,User};
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class RoleController {
 public function index() {
  $u=Access::require('roles.view');
  return view('roles.index',['roles'=>Role::with('permissions')->when(!$u->national(),fn($q)=>$q->where('scope','facility'))->get(),'permissions'=>Permission::all()]);
 }
 public function store(Request $r) { return $this->save($r,new Role); }
 public function update(Request $r,int $role) { Access::require('roles.manage'); return $this->save($r,Role::findOrFail($role)); }
 private function save(Request $r,Role $role) {
  Access::require('roles.manage'); abort_if($role->is_system,403,'الأدوار النظامية ثابتة. أنشئ دوراً مخصصاً بدلاً من تعديلها.');
  $d=$r->validate(['name'=>'required|string|max:120','permissions'=>'required|array|min:1','permissions.*'=>'required|integer|distinct|exists:permissions,id']);
  $permissions=Permission::whereIn('id',$d['permissions'])->get();
  abort_if($permissions->contains('facility_allowed',false),403,'الأدوار المخصصة في هذه النسخة محصورة بصلاحيات المؤسسة.');
  $ids=$permissions->pluck('id')->push(Permission::where('slug','dashboard.view')->value('id'))->unique()->all();
  DB::transaction(function() use($role,$d,$ids) {
   $before=$role->exists?$role->permissions->pluck('slug')->all():[];
   if(!$role->exists) $role->slug='custom_'.Str::lower(Str::random(16));
   $role->fill(['name'=>$d['name'],'scope'=>'facility','is_system'=>false])->save(); $role->permissions()->sync($ids);
   DB::table('sessions')->whereIn('user_id',User::where('role_id',$role->id)->select('id'))->delete();
   Access::log('role.saved','role',$role->id,['before'=>$before,'after'=>$role->permissions()->pluck('slug')->all()]);
  });
  return back()->with('success','تم حفظ الدور وتطبيق الصلاحيات. أُغلقت الجلسات المرتبطة به لإعادة التحقق.');
 }
}
