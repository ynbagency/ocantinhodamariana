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
        foreach (Screen::all() as $screen) {
            $age = $screen->last_seen_at
                ? now()->diffInSeconds($screen->last_seen_at)
                : PHP_INT_MAX;

            if ($age > 600) {
                $raise->execute(
                    'screen_offline',
                    'critical',
                    "screen:{$screen->id}:offline",
                    "Ecrã {$screen->name} sem heartbeat.",
                    ['seconds' => $age]
                );
            }
        }
    }
}
