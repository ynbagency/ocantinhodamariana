<?php
namespace App\Domain\Operations; use App\Models\OperationalEvent;
class RaiseOperationalEvent {public function execute(string $type,string $severity,string $key,string $message,array $context=[]):OperationalEvent{
 $existing=OperationalEvent::whereNull('resolved_at')->where('dedupe_key',$key)->latest()->first();
 if($existing){$existing->update(['severity'=>$severity,'message'=>$message,'context'=>$context]);return $existing;}
 return OperationalEvent::create(['type'=>$type,'severity'=>$severity,'dedupe_key'=>$key,'message'=>$message,'context'=>$context]);
}}
