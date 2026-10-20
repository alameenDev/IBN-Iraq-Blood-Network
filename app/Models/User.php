<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable {
 protected $guarded=['id'];
 protected $hidden=['password','remember_token'];
 protected function casts(): array { return ['password'=>'hashed','active'=>'boolean','must_change_password'=>'boolean']; }
 public function facility() { return $this->belongsTo(Facility::class); }
 public function role() { return $this->belongsTo(Role::class); }
 public function national(): bool { return $this->role?->scope==='national'; }
 public function allowed(string $permission): bool {
  return $this->active && $this->role && $this->role->permissions->contains('slug',$permission);
 }
}
