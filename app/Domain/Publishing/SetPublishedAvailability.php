<?php
namespace App\Domain\Publishing;
use App\Models\DailyMenu;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetPublishedAvailability
{
 public function execute(int $version, int $position, bool $available): Publication {
  return DB::transaction(function () use ($version,$position,$available) {
   $menu=DailyMenu::whereDate('service_date',today())->lockForUpdate()->firstOrFail();
   $publication=Publication::where('daily_menu_id',$menu->id)->where('status','published')->latest('version')->firstOrFail();
   if($publication->version!==$version) throw ValidationException::withMessages(['availability'=>'O menu foi atualizado entretanto. Atualize a página e tente novamente.']);
   $payload=$publication->payload; $found=false;
   foreach($payload['items'] as &$item) {
    if((int)$item['position']!==$position) continue;
    $found=true;
    if((bool)$item['available']===$available) return $publication;
    $item['available']=$available;
   }
   unset($item);
   if(!$found) throw ValidationException::withMessages(['availability'=>'O prato já não pertence ao menu publicado.']);
   if($menu->status==='published') $menu->items()->where('position',$position)->update(['available'=>$available]);
   $result=app(PublishDailyMenu::class)->execute($menu,$payload,true);
   app(\App\Domain\Operations\Audit::class)->write('daily-menu.availability',$result,['position'=>$position,'available'=>$available]);
   return $result;
  });
 }
}
