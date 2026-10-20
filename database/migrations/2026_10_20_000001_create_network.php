<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('facilities', function(Blueprint $t) {
   $t->id(); $t->string('code',32)->unique(); $t->string('name',180); $t->string('kind',32); $t->string('ownership',16);
   $t->string('governorate',80); $t->string('city',100)->nullable(); $t->string('address',240)->nullable();
   $t->string('phone',32)->nullable(); $t->string('email',180)->nullable(); $t->string('license_number',100)->nullable();
   $t->foreignId('parent_id')->nullable()->constrained('facilities')->restrictOnDelete();
   $t->string('status',20)->default('pending'); $t->boolean('collects_donations')->default(false); $t->timestamps();
   $t->index(['kind','status','governorate']);
  });
  Schema::create('permissions', function(Blueprint $t) { $t->id(); $t->string('slug',64)->unique(); $t->string('name',120); $t->boolean('facility_allowed'); });
  Schema::create('roles', function(Blueprint $t) { $t->id(); $t->string('slug',64)->unique(); $t->string('name',120); $t->string('scope',16); $t->boolean('is_system')->default(false); $t->timestamps(); });
  Schema::create('permission_role', function(Blueprint $t) {
   $t->foreignId('role_id')->constrained()->cascadeOnDelete(); $t->foreignId('permission_id')->constrained()->cascadeOnDelete(); $t->primary(['role_id','permission_id']);
  });
  Schema::create('users', function(Blueprint $t) {
   $t->id(); $t->string('name',120); $t->string('email',180)->unique(); $t->string('password');
   $t->foreignId('facility_id')->nullable()->constrained()->restrictOnDelete(); $t->foreignId('role_id')->constrained()->restrictOnDelete();
   $t->boolean('active')->default(true); $t->boolean('must_change_password')->default(true); $t->rememberToken(); $t->timestamps(); $t->index(['facility_id','active']);
  });
  Schema::create('facility_links', function(Blueprint $t) {
   $t->id(); $t->foreignId('bank_id')->constrained('facilities')->restrictOnDelete(); $t->foreignId('hospital_id')->constrained('facilities')->restrictOnDelete();
   $t->foreignId('requested_facility_id')->constrained('facilities')->restrictOnDelete(); $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
   $t->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete(); $t->string('status',20)->default('pending');
   $t->string('note',500); $t->string('decision_reason',500)->nullable(); $t->timestamp('reviewed_at')->nullable(); $t->timestamps();
   $t->unique(['bank_id','hospital_id']); $t->unique(['id','bank_id','hospital_id'],'link_endpoints_unique'); $t->index(['status','bank_id']);
  });
  Schema::create('blood_requests', function(Blueprint $t) {
   $t->id(); $t->string('reference',32)->unique(); $t->unsignedBigInteger('link_id'); $t->unsignedBigInteger('bank_id'); $t->unsignedBigInteger('hospital_id');
   $t->foreign(['link_id','bank_id','hospital_id'],'request_link_endpoints_fk')->references(['id','bank_id','hospital_id'])->on('facility_links')->restrictOnDelete();
   $t->foreignId('created_by')->constrained('users')->restrictOnDelete(); $t->string('request_type',16); $t->string('component',16); $t->string('blood_group',12);
   $t->unsignedSmallInteger('quantity'); $t->string('priority',16); $t->timestamp('needed_at'); $t->text('patient_reference')->nullable();
   $t->string('status',20)->default('submitted'); $t->string('decision_reason',500)->nullable(); $t->foreignId('decided_by')->nullable()->constrained('users')->restrictOnDelete();
   $t->timestamp('decided_at')->nullable(); $t->timestamps(); $t->index(['hospital_id','status']); $t->index(['bank_id','status','priority']);
  });
  Schema::create('donors', function(Blueprint $t) {
   $t->id(); $t->string('reference',32)->unique(); $t->foreignId('facility_id')->constrained()->restrictOnDelete();
   $t->string('identity_type',16); $t->string('identity_hash',64); $t->text('identity_number'); $t->text('full_name');
   $t->text('phone')->nullable(); $t->text('birth_date'); $t->string('sex',16); $t->timestamps();
   $t->unique(['facility_id','identity_hash']); $t->unique(['id','facility_id'],'donor_facility_unique');
  });
  Schema::create('donation_visits', function(Blueprint $t) {
   $t->id(); $t->string('reference',32)->unique(); $t->unsignedBigInteger('donor_id'); $t->unsignedBigInteger('facility_id');
   $t->foreign(['donor_id','facility_id'],'visit_donor_facility_fk')->references(['id','facility_id'])->on('donors')->restrictOnDelete();
   $t->foreignId('active_donor_id')->nullable()->unique()->constrained('donors')->restrictOnDelete();
   $t->string('status',24)->default('awaiting_screening'); $t->string('consent_version',60); $t->timestamp('consented_at');
   $t->foreignId('registered_by')->constrained('users')->restrictOnDelete(); $t->string('cancellation_reason',500)->nullable();
   $t->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete(); $t->timestamps(); $t->index(['facility_id','status']);
  });
  Schema::create('audit_events', function(Blueprint $t) {
   $t->id(); $t->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
   $t->foreignId('facility_id')->nullable()->constrained()->restrictOnDelete(); $t->string('action',80); $t->string('subject_type',80);
   $t->unsignedBigInteger('subject_id')->nullable(); $t->json('changes'); $t->string('ip',45)->nullable(); $t->timestamp('created_at');
   $t->index(['facility_id','created_at']); $t->index(['subject_type','subject_id']);
  });
  Schema::create('sessions', function(Blueprint $t) { $t->string('id')->primary(); $t->unsignedBigInteger('user_id')->nullable()->index(); $t->string('ip_address',45)->nullable(); $t->text('user_agent')->nullable(); $t->longText('payload'); $t->integer('last_activity')->index(); });
  Schema::create('cache', function(Blueprint $t) { $t->string('key')->primary(); $t->mediumText('value'); $t->integer('expiration'); });
  Schema::create('cache_locks', function(Blueprint $t) { $t->string('key')->primary(); $t->string('owner'); $t->integer('expiration'); });
 }
 public function down(): void {
  foreach(['cache_locks','cache','sessions','audit_events','donation_visits','donors','blood_requests','facility_links','users','permission_role','roles','permissions','facilities'] as $name) Schema::dropIfExists($name);
 }
};
