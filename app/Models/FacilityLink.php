<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FacilityLink extends Model {
 protected $guarded=['id'];
 protected function casts(): array { return ['reviewed_at'=>'datetime']; }
 public function bank() { return $this->belongsTo(Facility::class,'bank_id'); }
 public function hospital() { return $this->belongsTo(Facility::class,'hospital_id'); }
 public function requester() { return $this->belongsTo(User::class,'requested_by'); }
}
