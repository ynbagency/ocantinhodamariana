<?php
namespace App\Domain\Loyalty;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoyaltyLedger {
 public function earn(LoyaltyAccount $a,int $units,int $operatorId,?string $ref=null):LoyaltyTransaction {
  if($units<1) throw ValidationException::withMessages(['units'=>'As visitas a adicionar têm de ser positivas.']);
  return $a->transactions()->create(['type'=>'earn','units'=>$units,'operator_id'=>$operatorId,'reference'=>$ref]);
 }
 public function adjust(LoyaltyAccount $a,int $delta,int $operatorId,string $reason):LoyaltyTransaction {
  if($delta===0) throw ValidationException::withMessages(['units'=>'O ajuste não pode ser zero.']);
  return $a->transactions()->create(['type'=>'adjust','units'=>$delta,'operator_id'=>$operatorId,'reference'=>$reason]);
 }
 public function redeem(LoyaltyAccount $a,Reward $reward,int $operatorId):array {
  return DB::transaction(function()use($a,$reward,$operatorId){
   $locked=LoyaltyAccount::whereKey($a->id)->lockForUpdate()->firstOrFail();
   if(!$reward->active) throw ValidationException::withMessages(['reward'=>'Esta recompensa não está ativa.']);
   if($locked->balance()<$reward->cost_units) throw ValidationException::withMessages(['reward'=>'Saldo insuficiente.']);
   $tx=$locked->transactions()->create(['type'=>'redeem','units'=>-$reward->cost_units,'operator_id'=>$operatorId,'reference'=>'reward:'.$reward->id]);
   $plain=bin2hex(random_bytes(16));
   $red=RewardRedemption::create(['loyalty_account_id'=>$locked->id,'reward_id'=>$reward->id,'loyalty_transaction_id'=>$tx->id,'status'=>'issued','code_hash'=>hash('sha256',$plain)]);
   return [$red,$plain];
  });
 }
}
