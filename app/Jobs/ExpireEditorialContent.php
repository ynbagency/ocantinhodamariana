<?php
namespace App\Jobs; use App\Models\EditorialContent; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Foundation\Queue\Queueable;
class ExpireEditorialContent implements ShouldQueue {use Queueable; public function handle():void{EditorialContent::where('active',true)->whereNotNull('ends_at')->where('ends_at','<',now())->update(['active'=>false]);}}
