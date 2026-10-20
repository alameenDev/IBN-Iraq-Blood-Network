<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Donor extends Model {
 protected $guarded=['id'];
 protected $hidden=['full_name','identity_number','identity_hash','phone','birth_date'];
 protected function casts(): array { return ['full_name'=>'encrypted','identity_number'=>'encrypted','phone'=>'encrypted','birth_date'=>'encrypted']; }
 public function visits() { return $this->hasMany(DonationVisit::class)->latest(); }
}
