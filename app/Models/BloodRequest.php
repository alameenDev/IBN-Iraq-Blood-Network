<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BloodRequest extends Model {
 protected $guarded=['id'];
 protected $hidden=['patient_reference'];
 protected function casts(): array { return ['patient_reference'=>'encrypted','needed_at'=>'datetime','decided_at'=>'datetime']; }
 public function bank() { return $this->belongsTo(Facility::class,'bank_id'); }
 public function hospital() { return $this->belongsTo(Facility::class,'hospital_id'); }
 public function link() { return $this->belongsTo(FacilityLink::class,'link_id'); }
}
