<?php
use Illuminate\Support\Facades\{Artisan,DB,Hash,Validator};
use Illuminate\Validation\Rules\Password;
use App\Models\{User,Role};
use App\Support\Access;
Artisan::command('ibn:admin',function() {
 if(strlen((string)config('ibn.identity_hash_key'))<32) { $this->error('Configure IDENTITY_HASH_KEY (at least 32 random characters) first.'); return 1; }
 $this->call('db:seed',['--class'=>\Database\Seeders\DatabaseSeeder::class,'--force'=>true]);
 $name=$this->ask('Administrator name'); $email=mb_strtolower((string)$this->ask('Administrator email'));
 $password=$this->secret('Password (12+ chars, uppercase, lowercase, number and symbol)');
 $confirmation=$this->secret('Confirm password');
 $v=Validator::make(['name'=>$name,'email'=>$email,'password'=>$password,'password_confirmation'=>$confirmation],[
  'name'=>'required|string|max:120','email'=>'required|email|max:180|unique:users,email',
  'password'=>['required','confirmed','max:200',Password::min(12)->mixedCase()->numbers()->symbols()],
 ]);
 if($v->fails()) { foreach($v->errors()->all() as $error) $this->error($error); return 1; }
 DB::transaction(function() use($name,$email,$password) {
  $u=User::create(['name'=>$name,'email'=>$email,'password'=>Hash::make($password),'role_id'=>Role::where('slug','platform_admin')->firstOrFail()->id,
   'facility_id'=>null,'active'=>true,'must_change_password'=>false]); Access::log('bootstrap.admin_created','user',$u->id);
 });
 $this->info('Administrator created. No password was printed or stored in source.'); return 0;
})->purpose('Create an administrator interactively; there are no default credentials.');
Artisan::command('ibn:reset-password {email}',function() {
 $u=User::where('email',mb_strtolower($this->argument('email')))->first();
 if(!$u) { $this->error('Account not found.'); return 1; }
 $password=$this->secret('New temporary password'); $confirmation=$this->secret('Confirm password');
 $v=Validator::make(['password'=>$password,'password_confirmation'=>$confirmation],['password'=>['required','confirmed','max:200',Password::min(12)->mixedCase()->numbers()->symbols()]]);
 if($v->fails()) { foreach($v->errors()->all() as $error) $this->error($error); return 1; }
 DB::transaction(function() use($u,$password) {
  $u->update(['password'=>Hash::make($password),'must_change_password'=>true,'remember_token'=>null]);
  DB::table('sessions')->where('user_id',$u->id)->delete(); Access::log('bootstrap.password_reset','user',$u->id);
 }); $this->info('Password reset; existing sessions revoked.'); return 0;
})->purpose('Reset a staff account password locally through a trusted server administrator.');
Artisan::command('ibn:demo {--confirm}',function() {
 if(!app()->environment(['local','testing']) || !$this->option('confirm')) { $this->error('Allowed only in local/testing with --confirm. Use an isolated empty demo database.'); return 1; }
 if(\App\Models\Facility::exists()) { $this->error('Refusing: facilities already exist. Use a separate empty database.'); return 1; }
 if(!User::whereHas('role',fn($q)=>$q->where('slug','platform_admin'))->exists()) { $this->error('Create an administrator using ibn:admin first.'); return 1; }
 $this->call('db:seed',['--class'=>\Database\Seeders\DemoSeeder::class,'--force'=>true]);
 $this->info('Synthetic demonstration facilities and requests added. No staff passwords were created.'); return 0;
})->purpose('Seed clearly fictional data into an isolated non-production database.');
