<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Reward extends Model {protected $fillable=['name','cost_units','active'];protected function casts():array{return ['active'=>'boolean'];}}
