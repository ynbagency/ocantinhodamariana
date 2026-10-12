<?php
namespace App\Domain\Restaurant; use App\Models\RestaurantSetting;
class RestaurantFacts {
 public const KEYS=['name','claim','phone','address','postal_code','locality','opening_hours','takeaway_note','maps_url'];
 public function all():array{return RestaurantSetting::whereIn('key',self::KEYS)->pluck('value','key')->all();}
 public function get(string $key,?string $default=null):?string{return RestaurantSetting::where('key',$key)->value('value')??$default;}
 public function complete():bool{foreach(['phone','address','opening_hours'] as $k)if(blank($this->get($k)))return false;return true;}
}
