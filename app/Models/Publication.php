<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Publication extends Model {
 protected $fillable=['daily_menu_id','version','payload','status'];
 protected function casts(): array { return ['payload'=>'array']; }
 public function attempts(): HasMany { return $this->hasMany(PublicationAttempt::class); }
}
