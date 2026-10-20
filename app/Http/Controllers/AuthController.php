<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Hash};
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Support\Access;
class AuthController {
 public function login() { return view('auth.login'); }
 public function authenticate(Request $r) {
  $data=$r->validate(['email'=>'required|email|max:180','password'=>'required|string|max:200']);
  if(!Auth::attempt(['email'=>mb_strtolower($data['email']),'password'=>$data['password'],'active'=>true])) {
   throw ValidationException::withMessages(['email'=>'تعذر تسجيل الدخول. تحقق من البيانات أو راجع مسؤولك.']);
  }
  $u=$r->user();
  if(!$u->role || (!$u->national() && (!$u->facility || $u->facility->status!=='active'))) {
   Auth::logout(); throw ValidationException::withMessages(['email'=>'تعذر تسجيل الدخول. تحقق من البيانات أو راجع مسؤولك.']);
  }
  $r->session()->regenerate(); Access::log('auth.login','user',$u->id);
  return redirect()->intended(route('dashboard'));
 }
 public function logout(Request $r) {
  Access::log('auth.logout','user',$r->user()->id); Auth::logout();
  $r->session()->invalidate(); $r->session()->regenerateToken(); return redirect()->route('login');
 }
 public function password() { return view('auth.password'); }
 public function updatePassword(Request $r) {
  $data=$r->validate(['current_password'=>'required|current_password','password'=>['required','confirmed','different:current_password','max:200',Password::min(12)->mixedCase()->numbers()->symbols()]]);
  DB::transaction(function() use($r,$data) {
   $u=$r->user(); $u->update(['password'=>Hash::make($data['password']),'must_change_password'=>false,'remember_token'=>null]);
   DB::table('sessions')->where('user_id',$u->id)->delete(); Access::log('auth.password_changed','user',$u->id);
  });
  $r->session()->regenerate(); return redirect()->route('dashboard')->with('success','تم تغيير كلمة المرور وإنهاء الجلسات السابقة.');
 }
}
