<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditEvent extends Model {
 public $timestamps=false; protected $guarded=['id'];
 protected function casts(): array { return ['changes'=>'array','created_at'=>'datetime']; }
 protected static function booted(): void {
  static::updating(fn()=>throw new \LogicException('Audit events are append-only.'));
  static::deleting(fn()=>throw new \LogicException('Audit events cannot be deleted through the application.'));
 }
 public function actor() { return $this->belongsTo(User::class,'actor_id'); }
 public function facility() { return $this->belongsTo(Facility::class); }
}
