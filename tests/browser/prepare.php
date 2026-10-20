<?php
// CI-only fixture preparation. Never run against a real environment.
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(!app()->environment('testing') || !getenv('IBN_BROWSER_PASSWORD')) throw new \RuntimeException('Testing environment and ephemeral password required.');
\Illuminate\Support\Facades\Artisan::call('db:seed',['--force'=>true]);
\App\Models\User::create(['name'=>'مدير العرض التجريبي','email'=>'admin@example.test','password'=>getenv('IBN_BROWSER_PASSWORD'),
 'role_id'=>\App\Models\Role::where('slug','platform_admin')->firstOrFail()->id,'facility_id'=>null,'active'=>true,'must_change_password'=>false]);
\Illuminate\Support\Facades\Artisan::call('ibn:demo',['--confirm'=>true]);
