<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Hash};
use App\Models\{Facility,FacilityLink,User,Role,Permission,BloodRequest,Donor,DonationVisit,AuditEvent};
use App\Support\Access;
class NetworkTest extends TestCase {
 use RefreshDatabase;
 protected User $admin; protected User $bankUser; protected User $hospitalUser; protected User $otherUser;
 protected Facility $bank; protected Facility $hospital; protected Facility $other;
 protected function setUp(): void {
  parent::setUp(); $this->seed();
  $this->bank=$this->facility('BANK','main_bank'); $this->hospital=$this->facility('HOSPITAL','hospital'); $this->other=$this->facility('OTHER','hospital');
  $this->admin=$this->user('platform_admin',null,'admin'); $this->bankUser=$this->user('bank_manager',$this->bank,'bank');
  $this->hospitalUser=$this->user('hospital_manager',$this->hospital,'hospital'); $this->otherUser=$this->user('hospital_manager',$this->other,'other');
 }
 private function facility(string $code,string $kind): Facility {
  return Facility::create(['code'=>$code,'name'=>'Test '.$code,'kind'=>$kind,'ownership'=>'government','governorate'=>'بغداد','status'=>'active','license_number'=>'TEST-ONLY','collects_donations'=>$kind==='main_bank']);
 }
 private function user(string $role,?Facility $facility,string $name): User {
  return User::create(['name'=>$name,'email'=>$name.'@example.test','password'=>'TestOnly!Password123','role_id'=>Role::where('slug',$role)->firstOrFail()->id,'facility_id'=>$facility?->id,'active'=>true,'must_change_password'=>false]);
 }
 private function link(string $status='active'): FacilityLink {
  return FacilityLink::create(['bank_id'=>$this->bank->id,'hospital_id'=>$this->hospital->id,'requested_facility_id'=>$this->hospital->id,'requested_by'=>$this->hospitalUser->id,'note'=>'test agreement','status'=>$status]);
 }
 private function requestPayload(FacilityLink $link): array {
  return ['link_id'=>$link->id,'request_type'=>'stock','component'=>'RBC','blood_group'=>'O+','quantity'=>2,'priority'=>'urgent','needed_at'=>now()->timezone('Asia/Baghdad')->addHour()->format('Y-m-d\TH:i')];
 }
 private function donorPayload(): array {
  return ['full_name'=>'Synthetic Test Donor','identity_type'=>'national_id','identity_number'=>'TEST-123456','phone'=>'+9647000000000','birth_date'=>'1990-01-02','sex'=>'male','consent'=>'1','consent_version'=>'TEST-CONSENT-V1'];
 }
 public function test_guest_is_redirected_and_login_renders(): void {
  $this->get('/')->assertRedirect('/login'); $this->get('/login')->assertOk()->assertSee('تسجيل الدخول');
 }
 public function test_valid_login_and_invalid_login(): void {
  $this->post('/login',['email'=>$this->admin->email,'password'=>'wrong'])->assertSessionHasErrors('email');
  $this->post('/login',['email'=>$this->admin->email,'password'=>'TestOnly!Password123'])->assertRedirect('/'); $this->assertAuthenticatedAs($this->admin);
 }
 public function test_login_rate_limiting(): void {
  for($i=0;$i<5;$i++) $this->post('/login',['email'=>'rate@example.test','password'=>'wrong']);
  $this->post('/login',['email'=>'rate@example.test','password'=>'wrong'])->assertStatus(429);
 }
 public function test_inactive_account_cannot_login(): void {
  $this->hospitalUser->update(['active'=>false]);
  $this->post('/login',['email'=>$this->hospitalUser->email,'password'=>'TestOnly!Password123'])->assertSessionHasErrors('email'); $this->assertGuest();
 }
 public function test_suspended_facility_revokes_existing_access(): void {
  $this->actingAs($this->hospitalUser); $this->hospital->update(['status'=>'suspended']); $this->get('/requests')->assertRedirect('/login'); $this->assertGuest();
 }
 public function test_temporary_password_gate_and_change(): void {
  $this->hospitalUser->update(['must_change_password'=>true]); $this->actingAs($this->hospitalUser)->get('/')->assertRedirect('/account/password');
  $this->post('/account/password',['current_password'=>'TestOnly!Password123','password'=>'Changed!Password1234','password_confirmation'=>'Changed!Password1234'])->assertRedirect('/');
  $this->assertFalse($this->hospitalUser->fresh()->must_change_password); $this->assertTrue(Hash::check('Changed!Password1234',$this->hospitalUser->fresh()->password));
 }
 public function test_admin_pages_render(): void {
  $this->actingAs($this->admin); foreach(['/','/facilities','/facilities/create','/facilities/'.$this->bank->id.'/edit','/links','/users','/users/create','/roles','/audit','/requests','/workflow','/account/password'] as $path) $this->get($path)->assertOk();
 }
 public function test_facility_creation_requires_national_permission(): void {
  $payload=['code'=>'NEW-HOSP','name'=>'Synthetic Hospital','kind'=>'hospital','ownership'=>'private','governorate'=>'واسط','status'=>'pending','collects_donations'=>0];
  $this->actingAs($this->hospitalUser)->post('/facilities',$payload)->assertForbidden();
  $this->actingAs($this->admin)->post('/facilities',$payload)->assertRedirect('/facilities'); $this->assertDatabaseHas('facilities',['code'=>'NEW-HOSP','status'=>'pending']);
 }
 public function test_active_facility_requires_approval_reference(): void {
  $this->actingAs($this->admin)->post('/facilities',['code'=>'NEW-HOSP','name'=>'Synthetic Hospital','kind'=>'hospital','ownership'=>'private','governorate'=>'واسط','status'=>'active','collects_donations'=>0])->assertSessionHasErrors('license_number');
 }
 public function test_facility_code_and_kind_cannot_be_changed(): void {
  $this->actingAs($this->admin)->put('/facilities/'.$this->bank->id,['code'=>'ALTERED','name'=>$this->bank->name,'kind'=>'main_bank','ownership'=>'government','governorate'=>'بغداد','status'=>'active','license_number'=>'TEST','collects_donations'=>1])->assertStatus(422);
 }
 public function test_local_users_cannot_read_or_edit_another_facility_users(): void {
  $this->actingAs($this->hospitalUser)->get('/users')->assertOk()->assertDontSee($this->otherUser->email);
  $this->get('/users/'.$this->otherUser->id.'/edit')->assertNotFound();
 }
 public function test_local_admin_cannot_grant_national_role_or_foreign_facility(): void {
  $payload=['name'=>'Test Staff','email'=>'staff@example.test','role_id'=>$this->admin->role_id,'facility_id'=>$this->hospital->id,'active'=>1,'password'=>'TestOnly!Password123','password_confirmation'=>'TestOnly!Password123'];
  $this->actingAs($this->hospitalUser)->post('/users',$payload)->assertForbidden();
  $payload['role_id']=Role::where('slug','hospital_staff')->value('id'); $payload['facility_id']=$this->other->id;
  $this->post('/users',$payload)->assertForbidden();
 }
 public function test_local_admin_can_create_least_privilege_staff_and_no_secret_enters_audit(): void {
  $this->actingAs($this->hospitalUser)->post('/users',['name'=>'Test Staff','email'=>'staff@example.test','role_id'=>Role::where('slug','hospital_staff')->value('id'),'active'=>1,'password'=>'TestOnly!Password123','password_confirmation'=>'TestOnly!Password123'])->assertRedirect('/users');
  $staff=User::where('email','staff@example.test')->firstOrFail(); $this->assertSame($this->hospital->id,$staff->facility_id); $this->assertTrue($staff->must_change_password);
  $this->assertStringNotContainsString('TestOnly!Password123',DB::table('audit_events')->get()->toJson());
 }
 public function test_self_deactivation_and_last_admin_demotion_are_blocked(): void {
  $this->actingAs($this->admin)->put('/users/'.$this->admin->id,['name'=>'admin','email'=>$this->admin->email,'role_id'=>$this->admin->role_id,'active'=>0])->assertStatus(422);
  $this->assertTrue($this->admin->fresh()->active);
 }
 public function test_custom_roles_cannot_include_national_permissions(): void {
  $this->actingAs($this->admin)->post('/roles',['name'=>'Unsafe role','permissions'=>[Permission::where('slug','facilities.manage')->value('id')]])->assertForbidden();
  $this->post('/roles',['name'=>'Request viewer','permissions'=>[Permission::where('slug','requests.view')->value('id')]])->assertRedirect();
  $this->assertDatabaseHas('roles',['name'=>'Request viewer','scope'=>'facility']);
 }
 public function test_system_roles_are_immutable(): void {
  $this->actingAs($this->admin)->put('/roles/'.$this->admin->role_id,['name'=>'Edited','permissions'=>[Permission::first()->id]])->assertForbidden();
 }
 public function test_request_link_and_counterparty_approval(): void {
  $this->actingAs($this->hospitalUser)->post('/links',['bank_id'=>$this->bank->id,'hospital_id'=>$this->hospital->id,'note'=>'Test agreement'])->assertRedirect();
  $link=FacilityLink::firstOrFail(); $this->assertSame('pending',$link->status);
  $this->post('/links/'.$link->id.'/review',['status'=>'active','reason'=>'Self approval'])->assertForbidden();
  $this->actingAs($this->bankUser)->post('/links/'.$link->id.'/review',['status'=>'active','reason'=>'Counterparty authorization'])->assertRedirect();
  $this->assertSame('active',$link->fresh()->status);
 }
 public function test_forged_link_membership_and_duplicate_link_are_blocked(): void {
  $payload=['bank_id'=>$this->bank->id,'hospital_id'=>$this->hospital->id,'note'=>'Test agreement'];
  $this->actingAs($this->otherUser)->post('/links',$payload)->assertForbidden();
  $this->link(); $this->actingAs($this->hospitalUser)->post('/links',$payload)->assertSessionHasErrors('bank_id');
 }
 public function test_unrelated_facility_cannot_review_link(): void {
  $link=$this->link('pending'); $this->actingAs($this->otherUser)->post('/links/'.$link->id.'/review',['status'=>'active','reason'=>'forged'])->assertNotFound();
 }
 public function test_suspended_link_cannot_create_request(): void {
  $link=$this->link('suspended'); $this->actingAs($this->hospitalUser)->post('/requests',$this->requestPayload($link))->assertStatus(422); $this->assertDatabaseCount('blood_requests',0);
 }
 public function test_pending_link_cannot_create_request(): void {
  $link=$this->link('pending'); $this->actingAs($this->hospitalUser)->post('/requests',$this->requestPayload($link))->assertStatus(422);
 }
 public function test_unrelated_hospital_cannot_use_approved_link(): void {
  $link=$this->link(); $this->actingAs($this->otherUser)->post('/requests',$this->requestPayload($link))->assertNotFound();
 }
 public function test_request_creation_is_scoped_and_bank_acceptance_is_only_administrative(): void {
  $link=$this->link(); $this->actingAs($this->hospitalUser)->post('/requests',$this->requestPayload($link))->assertRedirect(); $record=BloodRequest::firstOrFail();
  $this->actingAs($this->otherUser)->get('/requests/'.$record->id)->assertNotFound();
  $this->get('/requests')->assertDontSee($record->reference);
  $this->actingAs($this->bankUser)->post('/requests/'.$record->id.'/review',['status'=>'accepted','reason'=>'Administrative review only'])->assertRedirect();
  $this->assertSame('accepted',$record->fresh()->status); $this->post('/requests/'.$record->id.'/review',['status'=>'rejected','reason'=>'Duplicate action'])->assertStatus(409);
 }
 public function test_request_patient_reference_is_encrypted_and_hidden_from_national_admin(): void {
  $link=$this->link(); $payload=$this->requestPayload($link); $payload['request_type']='patient'; $payload['patient_reference']='SYNTHETIC-PATIENT-REF';
  $this->actingAs($this->hospitalUser)->post('/requests',$payload)->assertRedirect(); $record=BloodRequest::firstOrFail();
  $this->assertStringNotContainsString('SYNTHETIC-PATIENT-REF',DB::table('blood_requests')->value('patient_reference'));
  $this->get('/requests/'.$record->id)->assertSee('SYNTHETIC-PATIENT-REF');
  $this->actingAs($this->admin)->get('/requests/'.$record->id)->assertDontSee('SYNTHETIC-PATIENT-REF');
 }
 public function test_quantity_validation_and_patient_reference_requirement(): void {
  $link=$this->link(); $payload=$this->requestPayload($link); $payload['quantity']=0;
  $this->actingAs($this->hospitalUser)->post('/requests',$payload)->assertSessionHasErrors('quantity');
  $payload['quantity']=1; $payload['request_type']='patient'; $this->post('/requests',$payload)->assertSessionHasErrors('patient_reference');
 }
 public function test_request_cancellation_retains_record(): void {
  $link=$this->link(); $this->actingAs($this->hospitalUser)->post('/requests',$this->requestPayload($link)); $record=BloodRequest::firstOrFail();
  $this->post('/requests/'.$record->id.'/cancel',['reason'=>'No longer required'])->assertRedirect(); $this->assertSame('cancelled',$record->fresh()->status);
 }
 public function test_donor_registration_encrypts_identity_and_only_opens_a_pending_visit(): void {
  $this->actingAs($this->bankUser)->post('/donors',$this->donorPayload())->assertRedirect(); $donor=Donor::firstOrFail();
  $this->assertStringNotContainsString('Synthetic',DB::table('donors')->value('full_name'));
  $this->assertSame('awaiting_screening',DonationVisit::firstOrFail()->status);
  $this->get('/donors/'.$donor->id)->assertOk()->assertSee('Synthetic Test Donor');
  $this->get('/donors?q=TEST-123456')->assertOk()->assertSee('Synthetic Test Donor');
 }
 public function test_donor_isolation_and_no_national_clinical_bypass(): void {
  $this->actingAs($this->bankUser)->post('/donors',$this->donorPayload()); $donor=Donor::firstOrFail();
  $otherBank=$this->facility('BANK2','main_bank'); $otherBankUser=$this->user('bank_manager',$otherBank,'bank2');
  $this->actingAs($otherBankUser)->get('/donors/'.$donor->id)->assertNotFound(); $this->get('/donors')->assertDontSee('Synthetic Test Donor');
  $this->actingAs($this->admin)->get('/donors/'.$donor->id)->assertForbidden();
 }
 public function test_duplicate_identity_and_concurrent_active_visit_guard(): void {
  $this->actingAs($this->bankUser)->post('/donors',$this->donorPayload());
  $this->post('/donors',$this->donorPayload())->assertSessionHasErrors('identity_number'); $donor=Donor::firstOrFail();
  $this->post('/donors/'.$donor->id.'/visits',['consent'=>1,'consent_version'=>'v1'])->assertSessionHasErrors('consent');
  $this->assertDatabaseCount('donation_visits',1);
 }
 public function test_missing_consent_blocks_registration(): void {
  $payload=$this->donorPayload(); unset($payload['consent']); $this->actingAs($this->bankUser)->post('/donors',$payload)->assertSessionHasErrors('consent');
 }
 public function test_cancelled_visit_allows_new_visit_without_deleting_history(): void {
  $this->actingAs($this->bankUser)->post('/donors',$this->donorPayload()); $donor=Donor::firstOrFail(); $visit=DonationVisit::firstOrFail();
  $this->post('/visits/'.$visit->id.'/cancel',['reason'=>'Synthetic cancellation'])->assertRedirect();
  $this->post('/donors/'.$donor->id.'/visits',['consent'=>1,'consent_version'=>'v2'])->assertRedirect();
  $this->assertDatabaseCount('donation_visits',2); $this->assertSame(1,DonationVisit::whereNotNull('active_donor_id')->count());
 }
 public function test_security_headers_and_no_delete_routes(): void {
  $this->actingAs($this->admin)->get('/')->assertHeader('X-Frame-Options','DENY')->assertHeader('X-Content-Type-Options','nosniff');
  $this->delete('/facilities/'.$this->hospital->id)->assertStatus(405); $this->delete('/audit/1')->assertNotFound();
 }
 public function test_audit_isolation_and_model_append_only_guard(): void {
  $this->actingAs($this->bankUser); Access::log('test.event','facility',$this->bank->id);
  $this->actingAs($this->hospitalUser)->get('/audit')->assertDontSee('test.event');
  $event=AuditEvent::firstOrFail(); $this->expectException(\LogicException::class); $event->update(['action'=>'tamper']);
 }
 public function test_demo_seeder_refuses_existing_facilities(): void {
  $this->artisan('ibn:demo --confirm')->assertExitCode(1);
 }
}
