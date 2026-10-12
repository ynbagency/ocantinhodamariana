<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = [
        'title','disk','path','thumb_path','web_path','signage_path','mime_type',
        'width','height','size_bytes','sha256','focal_x','focal_y','created_by',
    ];

    protected function casts(): array
    {
        return ['focal_x'=>'float','focal_y'=>'float','width'=>'integer','height'=>'integer'];
    }

    public function preferredPath(string $target): string
    {
        return match ($target) {
            'thumb' => $this->thumb_path ?: $this->web_path ?: $this->path,
            'web' => $this->web_path ?: $this->path,
            'signage' => $this->signage_path ?: $this->web_path ?: $this->path,
            default => $this->path,
        };
    }
}
