<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,DashboardController,FacilityController,LinkController,UserController,RoleController,RequestController,DonorController,AuditController};
Route::middleware('guest')->group(function() {
 Route::get('/login',[AuthController::class,'login'])->name('login');
 Route::post('/login',[AuthController::class,'authenticate'])->middleware('throttle:login');
});
Route::middleware(['auth','active'])->group(function() {
 Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::get('/account/password',[AuthController::class,'password'])->name('password.edit');
 Route::post('/account/password',[AuthController::class,'updatePassword'])->name('password.update');
 Route::get('/',[DashboardController::class,'index'])->name('dashboard');
 Route::get('/workflow',[DashboardController::class,'workflow'])->name('workflow');
 Route::resource('facilities',FacilityController::class)->except(['show','destroy']);
 Route::get('/links',[LinkController::class,'index'])->name('links.index');
 Route::post('/links',[LinkController::class,'store'])->name('links.store');
 Route::post('/links/{link}/review',[LinkController::class,'review'])->name('links.review');
 Route::resource('users',UserController::class)->except(['show','destroy']);
 Route::get('/roles',[RoleController::class,'index'])->name('roles.index');
 Route::post('/roles',[RoleController::class,'store'])->name('roles.store');
 Route::put('/roles/{role}',[RoleController::class,'update'])->name('roles.update');
 Route::resource('requests',RequestController::class)->only(['index','create','store','show']);
 Route::post('/requests/{request}/review',[RequestController::class,'review'])->name('requests.review');
 Route::post('/requests/{request}/cancel',[RequestController::class,'cancel'])->name('requests.cancel');
 Route::resource('donors',DonorController::class)->only(['index','create','store','show']);
 Route::post('/donors/{donor}/visits',[DonorController::class,'visit'])->name('donors.visit');
 Route::post('/visits/{visit}/cancel',[DonorController::class,'cancelVisit'])->name('visits.cancel');
 Route::get('/audit',[AuditController::class,'index'])->name('audit.index');
});
