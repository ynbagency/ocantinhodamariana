<?php
namespace App\Console\Commands;
use Illuminate\Console\Command; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Storage;
class ProductionReadiness extends Command {
 protected $signature='cantinho:readiness'; protected $description='Validate production prerequisites without changing data.';
 public function handle():int{
  $checks=[];
  $checks['APP_ENV production']=app()->environment('production');
  $checks['APP_DEBUG disabled']=!config('app.debug');
  $checks['APP_KEY configured']=(bool)config('app.key');
  try{DB::select('select 1');$checks['database']=true;}catch(\Throwable){$checks['database']=false;}
  try{$name='health/readiness-'.bin2hex(random_bytes(4));Storage::disk('local')->put($name,'ok');$checks['storage']=Storage::disk('local')->exists($name);Storage::disk('local')->delete($name);}catch(\Throwable){$checks['storage']=false;}
  $checks['queue not sync']=config('queue.default')!=='sync';
  $checks['HTTPS APP_URL']=str_starts_with((string)config('app.url'),'https://');
  foreach($checks as $name=>$ok){$this->line(($ok?'[OK] ':'[FAIL] ').$name);}
  return in_array(false,$checks,true)?self::FAILURE:self::SUCCESS;
 }
}
