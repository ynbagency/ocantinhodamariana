<?php
namespace App\Http\Controllers\Operation;
use App\Domain\Operations\Audit; use App\Domain\Restaurant\RestaurantFacts; use App\Models\RestaurantSetting; use Illuminate\Http\Request; use Illuminate\Http\RedirectResponse; use Illuminate\View\View;
class RestaurantSettingsController {
 public function edit(RestaurantFacts $facts):View{return view('operation.settings',['facts'=>$facts->all()]);}
 public function update(Request $r,Audit $audit):RedirectResponse{$d=$r->validate(['name'=>'required|string|max:120','claim'=>'nullable|string|max:180','phone'=>'nullable|string|max:40','address'=>'nullable|string|max:255','postal_code'=>'nullable|string|max:20','locality'=>'nullable|string|max:120','opening_hours'=>'nullable|string|max:1000','takeaway_note'=>'nullable|string|max:500','maps_url'=>'nullable|url|max:500']);foreach($d as $k=>$v)RestaurantSetting::updateOrCreate(['key'=>$k],['value'=>$v,'public'=>true]);$audit->write('restaurant.settings.updated',null,['keys'=>array_keys($d)],$r);return back()->with('status','Dados do restaurante atualizados.');}
}
