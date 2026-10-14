<?php
namespace App\Http\Controllers;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
class PublicMediaController {
 public function __invoke(MediaAsset $asset,string $variant){
  abort_unless($asset->disk==='public' && in_array($variant,['thumb','web','signage'],true),404);
  $path=$asset->preferredPath($variant);$disk=Storage::disk('public');abort_unless($disk->exists($path),404);
  $mime=match(strtolower(pathinfo($path,PATHINFO_EXTENSION))){'png'=>'image/png','webp'=>'image/webp',default=>'image/jpeg'};
  return response($disk->get($path),200,['Content-Type'=>$mime,'Cache-Control'=>'public, max-age=86400','X-Content-Type-Options'=>'nosniff']);
 }
}
