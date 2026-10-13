<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class DailyMenu extends Model {
 protected $fillable=['service_date','status','price_cents','published_at','created_by','version','scheduled_at'];
 protected function casts(): array { return ['service_date'=>'date','published_at'=>'datetime','scheduled_at'=>'datetime']; }
 public function setServiceDateAttribute($value): void { $this->attributes['service_date']=\Illuminate\Support\Carbon::parse($value)->toDateString(); }
 public function items(): HasMany { return $this->hasMany(DailyMenuItem::class)->orderBy('position'); }
}
