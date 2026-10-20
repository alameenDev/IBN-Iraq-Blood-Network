<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ActiveAccount {
 public function handle(Request $r, Closure $next) {
  $u=$r->user(); $u?->load(['role.permissions','facility']);
  if (!$u || !$u->active || !$u->role || (!$u->national() && (!$u->facility || $u->facility->status!=='active'))) {
   Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken();
   return redirect()->route('login')->withErrors(['email'=>'الحساب أو المؤسسة غير فعال. راجع مسؤول الشبكة.']);
  }
  if($u->must_change_password && !$r->routeIs('password.*','logout')) return redirect()->route('password.edit');
  return $next($r);
 }
}
