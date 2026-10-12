<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
 protected $fillable=['name','email','phone','consented_at'];
 protected function casts():array{return ['consented_at'=>'datetime'];}
 public function loyaltyAccount(){return $this->hasOne(LoyaltyAccount::class);}
}
