<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Permission extends Model { public $timestamps=false; protected $guarded=['id']; protected function casts(): array { return ['facility_allowed'=>'boolean']; } }
