<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DailyMenuItem extends Model {
 protected $fillable=['daily_menu_id','category','name','description','position','available','menu_item_id','media_asset_id','allergens'];
 protected function casts(): array { return ['available'=>'boolean','allergens'=>'array']; }
 public function media(){return $this->belongsTo(MediaAsset::class,'media_asset_id');}
 public function menu(): BelongsTo { return $this->belongsTo(DailyMenu::class,'daily_menu_id'); }
}
