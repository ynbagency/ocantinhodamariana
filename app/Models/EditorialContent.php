<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EditorialContent extends Model {
 protected $fillable=['type','title','body','eyebrow','media_asset_id','cta_label','cta_url','duration_seconds','weight','active','starts_at','ends_at','created_by'];
 protected function casts(): array { return ['active'=>'boolean','starts_at'=>'datetime','ends_at'=>'datetime']; }
 public function media(): BelongsTo { return $this->belongsTo(MediaAsset::class,'media_asset_id'); }
 public function scopeLive($q){return $q->where('active',true)->where(fn($x)=>$x->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($x)=>$x->whereNull('ends_at')->orWhere('ends_at','>=',now()));}
}
