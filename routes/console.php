<?php
use App\Jobs\EvaluateScreenHealth; use App\Jobs\ExpireEditorialContent; use Illuminate\Support\Facades\Schedule;
Schedule::job(new EvaluateScreenHealth)->everyFiveMinutes()->withoutOverlapping();
Schedule::job(new ExpireEditorialContent)->everyMinute()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();
