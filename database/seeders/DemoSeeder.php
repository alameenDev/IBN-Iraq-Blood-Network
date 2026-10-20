<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\{Facility,FacilityLink,BloodRequest,User};
use App\Support\Access;
class DemoSeeder extends Seeder {
 public function run(): void {
  if(!app()->environment(['local','testing']) || Facility::exists()) throw new \LogicException('Demo seeding requires an empty local/test facility database.');
  DB::transaction(function() {
   $actor=User::whereHas('role',fn($q)=>$q->where('slug','platform_admin'))->firstOrFail();
   $bank=Facility::create(['code'=>'DEMO-BANK','name'=>'مصرف الرافدين — تجريبي','kind'=>'main_bank','ownership'=>'government','governorate'=>'بغداد','status'=>'active','license_number'=>'DEMO-NOT-A-LICENSE','collects_donations'=>true]);
   foreach([['DEMO-H01','مستشفى النور — تجريبي','بغداد','government'],['DEMO-H02','مستشفى الحياة — تجريبي','واسط','private'],['DEMO-H03','مستشفى السلام — تجريبي','بابل','government']] as $i=>[$code,$name,$gov,$ownership]) {
    $h=Facility::create(['code'=>$code,'name'=>$name,'kind'=>'hospital','ownership'=>$ownership,'governorate'=>$gov,'status'=>'active','license_number'=>'DEMO-NOT-A-LICENSE']);
    $link=FacilityLink::create(['bank_id'=>$bank->id,'hospital_id'=>$h->id,'requested_facility_id'=>$h->id,'requested_by'=>$actor->id,'status'=>$i===2?'pending':'active','note'=>'بيانات عرض مصطنعة — لا تمثل مؤسسة حقيقية']);
    if($i<2) foreach(['routine','urgent','emergency'] as $j=>$priority) BloodRequest::create(['reference'=>'REQ-'.Str::ulid(),'link_id'=>$link->id,'bank_id'=>$bank->id,'hospital_id'=>$h->id,'created_by'=>$actor->id,
     'request_type'=>'stock','component'=>['RBC','FFP','PLT'][$j],'blood_group'=>['O+','A+','B+'][$j],'quantity'=>($i+$j+1)*2,'priority'=>$priority,'needed_at'=>now()->addHours(4+$j),'status'=>$j===0?'accepted':'submitted']);
   }
   Access::log('demo.seeded','facility',$bank->id,['synthetic'=>true]);
  });
 }
}
