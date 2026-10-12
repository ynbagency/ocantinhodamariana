<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PublicationAttempt extends Model {
 protected $fillable=['publication_id','channel','status','error','completed_at'];
 protected function casts(): array { return ['completed_at'=>'datetime']; }
}
