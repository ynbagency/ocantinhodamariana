<?php
namespace App\Console\Commands;
use App\Domain\Publishing\PublishDailyMenu;
use App\Domain\Operations\RaiseOperationalEvent;
use App\Models\DailyMenu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublishScheduledMenus extends Command
{
 protected $signature='cantinho:publish-scheduled';
 protected $description='Publish due daily menus once; never publish a past service day.';
 public function handle(PublishDailyMenu $publisher): int {
  DailyMenu::whereNotNull('scheduled_at')->where('scheduled_at','<=',now())->orderBy('id')->chunkById(50,function($menus) use($publisher){
   foreach($menus as $candidate) DB::transaction(function() use($candidate,$publisher){
    $menu=DailyMenu::whereKey($candidate->id)->lockForUpdate()->firstOrFail();
    if(!$menu->scheduled_at || $menu->scheduled_at->isFuture()) return;
    if(!$menu->service_date->isToday()) {
     $menu->update(['scheduled_at'=>null,'status'=>'draft']);
     app(RaiseOperationalEvent::class)->execute('schedule_expired','warning','schedule:'.$menu->id,'O agendamento não foi executado antes do fim do dia.');
     return;
    }
    try { $publisher->execute($menu); }
    catch(\DomainException $e) {
     $menu->update(['scheduled_at'=>null,'status'=>'draft']);
     app(RaiseOperationalEvent::class)->execute('schedule_failed','warning','schedule:'.$menu->id,$e->getMessage());
    }
   });
  });
  return self::SUCCESS;
 }
}
