<?php
namespace App\Domain\Publishing;

use App\Models\DailyMenu;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;

class PublishDailyMenu
{
    public function execute(DailyMenu $menu, ?array $snapshot = null, bool $preserveDraft = false): Publication
    {
        return DB::transaction(function () use ($menu, $snapshot, $preserveDraft) {
            $menu = DailyMenu::query()->lockForUpdate()->findOrFail($menu->id);

            if (!$menu->service_date->isToday()) throw new \DomainException('Só pode publicar o menu do dia atual.');
            if ($snapshot === null && $menu->items()->where('available', true)->count() === 0) {
                throw new \DomainException('É necessário pelo menos um prato disponível.');
            }

            $nextVersion = $menu->version + 1;
            $payload = $snapshot ?? [
                'date' => $menu->service_date->toDateString(),
                'price_cents' => $menu->price_cents,
                'items' => $menu->items()->with('media')->orderBy('position')->get()->map(function($item){
                    $values=$item->only(['category','name','description','available','position','menu_item_id','media_asset_id','allergens']);
                    $values['photo']=$item->media ? ['web_url'=>\Illuminate\Support\Facades\Storage::disk($item->media->disk)->url($item->media->preferredPath('web')),'focal_x'=>(float)$item->media->focal_x,'focal_y'=>(float)$item->media->focal_y] : null;
                    return $values;
                })->all(),
            ];

            $publication = Publication::create([
                'daily_menu_id' => $menu->id,
                'version' => $nextVersion,
                'payload' => $payload,
                'status' => 'processing',
            ]);

            // Website and signage are first-party destinations: publication of the
            // immutable snapshot makes them immediately available. External social
            // channels remain pending until their credentials/adapters are configured.
            foreach (['website', 'signage'] as $channel) {
                $publication->attempts()->create([
                    'channel' => $channel,
                    'status' => 'published',
                    'completed_at' => now(),
                ]);
            }
            foreach (['facebook', 'instagram'] as $channel) {
                $publication->attempts()->create(['channel' => $channel, 'status' => 'pending']);
            }

            $menu->forceFill([
                'version' => $nextVersion,
                'status' => $preserveDraft ? $menu->status : 'published',
                'scheduled_at' => $preserveDraft ? $menu->scheduled_at : null,
                'published_at' => now(),
            ])->save();

            $publication->update(['status' => 'published']);
            app(\App\Domain\Operations\Audit::class)->write('daily-menu.published',$publication,['version'=>$nextVersion]);
            return $publication->fresh('attempts');
        });
    }
}
