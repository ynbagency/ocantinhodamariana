<?php
namespace App\Domain\Social; use App\Models\Publication; use RuntimeException;
class MetaPublisher implements SocialPublisher {
 public function __construct(private string $channel){}
 public function channel():string{return $this->channel;}
 public function publish(Publication $p):array{
  if(!config('services.meta.enabled')) throw new RuntimeException('Publicação Meta não configurada.');
  // Gate 8 deliberately isolates the provider boundary. Real Graph API calls
  // require approved account/page tokens and are not simulated.
  throw new RuntimeException('Credenciais Meta reais ainda não disponíveis.');
 }
}
