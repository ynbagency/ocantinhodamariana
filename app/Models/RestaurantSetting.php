<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class RestaurantSetting extends Model {protected $fillable=['key','value','public'];protected function casts():array{return ['public'=>'boolean'];}}
