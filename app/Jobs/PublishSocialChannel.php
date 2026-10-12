<?php
namespace App\Jobs;
use App\Domain\Operations\RaiseOperationalEvent; use App\Domain\Social\MetaPublisher; use App\Models\Publication;
use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Foundation\Queue\Queueable; use Throwable;
class PublishSocialChannel implements ShouldQueue {
 use Queueable; public int $tries=5; public array $backoff=[60,300,900,3600];
 public function __construct(public int $publicationId,public string $channel){}
 public function handle():void{$p=Publication::findOrFail($this->publicationId);(new MetaPublisher($this->channel))->publish($p);}
 public function failed(Throwable $e):void{app(RaiseOperationalEvent::class)->execute('social_publish_failed','warning',"social:{$this->channel}:{$this->publicationId}","Falha persistente ao publicar em {$this->channel}.",['error'=>$e->getMessage()]);}
}
