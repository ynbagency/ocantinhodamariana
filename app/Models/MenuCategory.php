<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MenuCategory extends Model {
 protected $fillable=['name','kind','position','active'];
 protected function casts():array{return ['active'=>'boolean'];}
 public function items(){return $this->hasMany(MenuItem::class);}
}
