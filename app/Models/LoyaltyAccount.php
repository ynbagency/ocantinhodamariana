<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoyaltyAccount extends Model {
 protected $fillable=['customer_id','active']; protected function casts():array{return ['active'=>'boolean'];}
 public function customer(){return $this->belongsTo(Customer::class);}
 public function transactions(){return $this->hasMany(LoyaltyTransaction::class);}
 public function balance():int{return (int)$this->transactions()->sum('units');}
}
