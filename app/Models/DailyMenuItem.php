<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DailyMenuItem extends Model {
 protected $fillable=['daily_menu_id','category','name','description','position','available'];
 protected function casts(): array { return ['available'=>'boolean']; }
 public function menu(): BelongsTo { return $this->belongsTo(DailyMenu::class,'daily_menu_id'); }
}
