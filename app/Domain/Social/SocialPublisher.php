<?php
namespace App\Domain\Social; use App\Models\Publication;
interface SocialPublisher {public function channel():string; public function publish(Publication $publication):array;}
