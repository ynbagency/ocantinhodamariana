<?php
namespace App\Http\Controllers;
use App\Models\DailyMenu;
use App\Models\Publication;
use App\Models\EditorialContent;
use Illuminate\View\View;
class PublicSiteController {
 private function todayPublication(){
  $m=DailyMenu::whereDate('service_date',today())->first();
  return $m?Publication::where('daily_menu_id',$m->id)->where('status','published')->latest('version')->first():null;
 }
 public function home():View{return view('public.home',['publication'=>$this->todayPublication(),'stories'=>EditorialContent::live()->with('media')->take(6)->get()]);}
 public function menu():View{return view('public.menu',['publication'=>$this->todayPublication(),'categories'=>\App\Models\MenuCategory::where('active',true)->with(['items'=>fn($q)=>$q->where('active',true)->with('media')->orderBy('name')])->orderBy('position')->orderBy('name')->get()]);}
 public function story():View{return view('public.story',['stories'=>EditorialContent::live()->whereIn('type',['bulgaria','mariana'])->with('media')->take(12)->get()]);}
 public function takeaway():View{return view('public.takeaway',['publication'=>$this->todayPublication()]);}
 public function contacts():View{return view('public.contacts');}
 public function loyalty():View{return view('pwa.loyalty');}
}
