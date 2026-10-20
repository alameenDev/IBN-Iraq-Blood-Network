<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
class AppServiceProvider extends ServiceProvider {
 public function register(): void {}
 public function boot(): void {
  RateLimiter::for('login',fn(Request $r)=>[
   Limit::perMinute(5)->by('login:'.hash('sha256',mb_strtolower((string)$r->input('email'))).'|'.$r->ip()),
   Limit::perMinute(30)->by('login-ip:'.$r->ip()),
  ]);
 }
}
