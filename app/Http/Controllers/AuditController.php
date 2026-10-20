<?php
namespace App\Http\Controllers;
use App\Support\Access;
class AuditController {
 public function index() { $u=Access::require('audit.view'); return view('audit.index',['events'=>Access::audit($u)->with(['actor','facility'])->latest('id')->paginate(25)]); }
}
