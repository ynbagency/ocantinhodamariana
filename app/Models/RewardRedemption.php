<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RewardRedemption extends Model {
 protected $fillable=['loyalty_account_id','reward_id','loyalty_transaction_id','status','code_hash','expires_at','used_at'];
 protected function casts():array{return ['expires_at'=>'datetime','used_at'=>'datetime'];}
}
