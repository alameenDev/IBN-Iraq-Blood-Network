<?php
namespace App\Support;
use App\Models\{User,Facility,FacilityLink,BloodRequest,Donor,DonationVisit,AuditEvent};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
final class Access {
 public static function require(string $permission): User {
  $u=Auth::user(); abort_unless($u && $u->allowed($permission),403,'ليس لديك صلاحية تنفيذ هذه العملية.'); return $u;
 }
 public static function facility(User $u): Facility {
  abort_if($u->national() || !$u->facility_id,403,'تحتاج هذه العملية حساب مؤسسة محددة.');
  $f=$u->facility; abort_unless($f && $f->status==='active',403,'المؤسسة غير فعالة.'); return $f;
 }
 public static function links(User $u): Builder {
  return FacilityLink::query()->when(!$u->national(),fn($q)=>$q->where(fn($q)=>$q->where('bank_id',$u->facility_id)->orWhere('hospital_id',$u->facility_id)));
 }
 public static function requests(User $u): Builder {
  return BloodRequest::query()->when(!$u->national(),fn($q)=>$q->where(fn($q)=>$q->where('bank_id',$u->facility_id)->orWhere('hospital_id',$u->facility_id)));
 }
 public static function users(User $u): Builder {
  return User::query()->when(!$u->national(),fn($q)=>$q->where('facility_id',$u->facility_id));
 }
 public static function donors(User $u): Builder {
  $f=self::facility($u); abort_unless($f->collects_donations,403,'تسجيل التبرعات غير مفعل لهذه المؤسسة.');
  return Donor::query()->where('facility_id',$f->id);
 }
 public static function audit(User $u): Builder { return AuditEvent::query()->when(!$u->national(),fn($q)=>$q->where('facility_id',$u->facility_id)); }
 public static function log(string $action, string $type, ?int $id, array $changes=[], ?int $facilityId=null): void {
  // Callers must pass a strict metadata allow-list. Never pass request->all(), credentials or clinical identities.
  AuditEvent::create(['actor_id'=>Auth::id(),'facility_id'=>$facilityId ?? Auth::user()?->facility_id,
   'action'=>$action,'subject_type'=>$type,'subject_id'=>$id,'changes'=>$changes,'ip'=>app()->runningInConsole()?null:request()->ip(),'created_at'=>now()]);
 }
 public static function identityHash(string $type,string $identity): string {
  $key=config('ibn.identity_hash_key'); abort_unless(is_string($key)&&strlen($key)>=32,503,'يلزم ضبط مفتاح حماية البحث عن الهوية.');
  $value=strtr(trim($identity),array_combine(preg_split('//u','٠١٢٣٤٥٦٧٨٩۰۱۲۳۴۵۶۷۸۹',-1,PREG_SPLIT_NO_EMPTY),str_split('01234567890123456789')));
  $value=preg_replace('/[\s-]+/u','',mb_strtoupper($value));
  return hash_hmac('sha256',$type.':'.$value,$key);
 }
}
