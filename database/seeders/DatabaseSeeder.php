<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{Role,Permission};
use App\Support\Catalog;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  DB::transaction(function() {
   foreach(Catalog::PERMISSIONS as $slug=>[$name,$local]) Permission::updateOrCreate(['slug'=>$slug],['name'=>$name,'facility_allowed'=>$local]);
   foreach(Catalog::ROLES as $slug=>[$name,$scope,$permissions]) {
    $role=Role::updateOrCreate(['slug'=>$slug],['name'=>$name,'scope'=>$scope,'is_system'=>true]);
    $role->permissions()->sync(Permission::whereIn('slug',$permissions)->pluck('id'));
   }
  });
 }
}
