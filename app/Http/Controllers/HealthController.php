<?php
namespace App\Http\Controllers; use Illuminate\Http\JsonResponse; use Illuminate\Support\Facades\DB;
class HealthController {public function __invoke():JsonResponse{$ok=true;$checks=[];try{DB::select('select 1');$checks['database']='ok';}catch(\Throwable $e){$ok=false;$checks['database']='error';}return response()->json(['ok'=>$ok,'checks'=>$checks,'time'=>now()->toIso8601String()],$ok?200:503)->header('Cache-Control','no-store');}}
