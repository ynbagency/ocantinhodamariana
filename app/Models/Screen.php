<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Screen extends Model {
 protected $fillable=['device_id','name','token_hash','enabled','player_version','content_version','last_seen_at','last_sync_at','last_error','meta'];
 protected $casts=['enabled'=>'boolean','last_seen_at'=>'datetime','last_sync_at'=>'datetime','meta'=>'array'];
 public function health(): string {
  if (!$this->last_seen_at || $this->last_seen_at->lt(now()->subMinutes(5))) return 'offline';
  if ($this->last_error) return 'warning';
  return 'online';
 }
}
