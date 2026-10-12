<?php
namespace App\Domain\Publishing;

use App\Models\DailyMenu;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;

class PublishDailyMenu
{
    public function execute(DailyMenu $menu): Publication
    {
        return DB::transaction(function () use ($menu) {
            $menu = DailyMenu::query()->lockForUpdate()->findOrFail($menu->id);

            if ($menu->items()->where('available', true)->count() === 0) {
                throw new \DomainException('É necessário pelo menos um prato disponível.');
            }

            $nextVersion = $menu->version + 1;
            $payload = [
                'date' => $menu->service_date->toDateString(),
                'price_cents' => $menu->price_cents,
                'items' => $menu->items()->orderBy('position')->get([
                    'category', 'name', 'description', 'available', 'position',
                ])->toArray(),
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
                'status' => 'published',
                'published_at' => now(),
            ])->save();

            $publication->update(['status' => 'published']);
            return $publication->fresh('attempts');
        });
    }
}
