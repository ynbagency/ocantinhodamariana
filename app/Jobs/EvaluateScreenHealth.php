<?php
namespace App\Jobs;

use App\Domain\Operations\RaiseOperationalEvent;
use App\Models\Screen;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluateScreenHealth implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $raise = app(RaiseOperationalEvent::class);
        foreach (Screen::where('enabled',true)->get() as $screen) {
            $age = $screen->last_seen_at
                ? $screen->last_seen_at->diffInSeconds(now())
                : PHP_INT_MAX;

            if ($age > 600) {
                $raise->execute(
                    'screen_offline',
                    'critical',
                    "screen:{$screen->id}:offline",
                    "Ecrã {$screen->name} sem heartbeat.",
                    ['seconds' => $age]
                );
            } else {
                \App\Models\OperationalEvent::where('dedupe_key',"screen:{$screen->id}:offline")->whereNull('resolved_at')->update(['resolved_at'=>now()]);
            }
        }
    }
}
