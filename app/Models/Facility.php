<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Facility extends Model {
 protected $guarded=['id'];
 protected function casts(): array { return ['collects_donations'=>'boolean']; }
 public function parent() { return $this->belongsTo(self::class,'parent_id'); }
 public function isHospital(): bool { return in_array($this->kind,['hospital','branch_bank'],true); }
}
