<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MenuItem extends Model {
 protected $fillable=['menu_category_id','name','description','price_cents','allergens','media_asset_id','active'];
 protected function casts():array{return ['active'=>'boolean','allergens'=>'array'];}
 public function category(){return $this->belongsTo(MenuCategory::class,'menu_category_id');}
 public function media(){return $this->belongsTo(MediaAsset::class,'media_asset_id');}
 public function scopeVisible($q){return $q->where('active',true)->whereHas('category',fn($q)=>$q->where('active',true));}
}
