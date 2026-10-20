<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DonationVisit extends Model {
 protected $guarded=['id'];
 protected function casts(): array { return ['consented_at'=>'datetime']; }
 public function donor() { return $this->belongsTo(Donor::class); }
}
