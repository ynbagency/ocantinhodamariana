<?php
namespace App\Http\Controllers\Operation;
use App\Domain\Loyalty\LoyaltyLedger;
use App\Models\Customer;
use App\Models\LoyaltyAccount;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class LoyaltyController {
 public function index():View{return view('operation.loyalty',['customers'=>Customer::with('loyaltyAccount')->latest()->paginate(30),'rewards'=>Reward::orderBy('cost_units')->get()]);}
 public function customer(Request $r):RedirectResponse{
  $d=$r->validate(['name'=>'required|string|max:120','phone'=>'nullable|string|max:40','email'=>'nullable|email|max:255']);
  $c=Customer::create($d+['consented_at'=>now()]);$c->loyaltyAccount()->create(['active'=>true]);return back()->with('status','Cliente criado.');
 }
 public function earn(Request $r,LoyaltyAccount $account,LoyaltyLedger $ledger):RedirectResponse{
  $d=$r->validate(['units'=>'required|integer|min:1|max:20']);$ledger->earn($account,$d['units'],$r->user()->id,'operator');return back()->with('status','Visita registada.');
 }
 public function reward(Request $r):RedirectResponse{
  $d=$r->validate(['name'=>'required|string|max:120','cost_units'=>'required|integer|min:1|max:1000']);
  Reward::create($d+['active'=>$r->boolean('active')]);return back()->with('status','Recompensa criada.');
 }
}
