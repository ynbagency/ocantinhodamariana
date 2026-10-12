<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoyaltyTransaction extends Model {
 protected $fillable=['loyalty_account_id','type','units','reference','operator_id','metadata'];
 protected function casts():array{return ['metadata'=>'array'];}
 public function account(){return $this->belongsTo(LoyaltyAccount::class,'loyalty_account_id');}
}
