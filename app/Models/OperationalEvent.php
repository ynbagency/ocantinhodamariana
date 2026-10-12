<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class OperationalEvent extends Model {protected $fillable=['type','severity','dedupe_key','message','context','resolved_at'];protected function casts():array{return ['context'=>'array','resolved_at'=>'datetime'];}}
