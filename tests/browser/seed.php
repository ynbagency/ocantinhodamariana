<?php
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Models\User::create(['name'=>'Operador de teste','email'=>'browser@example.test','password'=>'Browser-test-password-123','role'=>'admin']);
App\Models\DailyMenu::create(['service_date'=>today(),'status'=>'scheduled','scheduled_at'=>today()->endOfDay()->subMinute()]);
App\Models\OperationalEvent::create(['type'=>'schedule_expired','severity'=>'warning','message'=>'Technical detail excluded from UI']);
