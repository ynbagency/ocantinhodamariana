<?php
namespace App\Http\Controllers\Api;

use App\Domain\Signage\BuildEditorialPlaylist;
use App\Models\DailyMenu;
use App\Models\MediaAsset;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SignageController
{
    public function manifest(Request $request, string $deviceId, BuildEditorialPlaylist $playlist): JsonResponse
    {
        abort_unless($request->attributes->get('screen')->device_id === $deviceId,403);
        $menu=DailyMenu::whereDate('service_date',today())->first();
        $publication=$menu ? Publication::where('daily_menu_id',$menu->id)->where('status','published')->latest('version')->first() : null;

        if (!$publication) return response()->json([
            'schema'=>1,'version'=>'fallback','generated_at'=>now()->toIso8601String(),
            'layout'=>'fallback','content'=>['message'=>'Pergunte-nos pelos pratos de hoje'],'assets'=>[]
        ])->header('Cache-Control','no-store');

        $editorial=$playlist->build();
        $dailyPhotos=[];
        foreach($publication->payload['items']??[] as $item){
            if(!($item['available']??true)||empty($item['media_asset_id'])) continue;
            $asset=MediaAsset::find($item['media_asset_id']); if(!$asset)continue;
            $dailyPhotos[]=['type'=>'food','eyebrow'=>'PRATOS DE HOJE','title'=>$item['name'],'body'=>$item['description']??'','duration'=>12,'media'=>['id'=>$asset->id,'local_path'=>'media/'.$asset->id.'.'.strtolower(pathinfo($asset->preferredPath('signage'),PATHINFO_EXTENSION)),'focal_x'=>$item['photo']['focal_x']??.5,'focal_y'=>$item['photo']['focal_y']??.5]];
        }
        $editorial=array_merge($dailyPhotos,$editorial);
        $assets=[];
        foreach($editorial as &$slide){
            if(empty($slide['media']['id'])) continue;
            $asset=MediaAsset::find($slide['media']['id']);
            if(!$asset) { $slide['media']=null; continue; }
            $path=$asset->preferredPath('signage');
            if(!Storage::disk($asset->disk)->exists($path)) { $slide['media']=null; continue; }
            $assets[$slide['media']['local_path']]=$this->snapshotAsset(
                $slide['media']['local_path'], Storage::disk($asset->disk)->get($path)
            );
        }
        unset($slide);
        $assets['index.html']=$this->snapshotAsset('index.html',$this->renderReleaseHtml($publication->payload,$editorial));
        ksort($assets);
        $assets=array_values($assets);
        $version='bundle-'.hash('sha256',json_encode($assets,JSON_THROW_ON_ERROR));

        return response()->json([
            'schema'=>1,'version'=>$version,
            'generated_at'=>now()->toIso8601String(),'layout'=>'today',
            'content'=>array_merge($publication->payload,['editorial'=>$editorial]),'assets'=>$assets,
        ])->header('Cache-Control','no-store');
    }

    private function snapshotAsset(string $path, string $bytes): array
    {
        $digest=hash('sha256',$bytes);
        $disk=Storage::disk('local');
        $stored='signage/blobs/'.$digest;
        if(!$disk->exists($stored)) $disk->put($stored,$bytes);
        return ['path'=>$path,'url'=>route('signage.bundle',['digest'=>$digest,'filename'=>basename($path)],false),'sha256'=>$digest];
    }

    public function bundle(string $digest, string $filename): Response
    {
        abort_unless(preg_match('/^[a-f0-9]{64}$/D',$digest),404);
        $disk=Storage::disk('local'); $path='signage/blobs/'.$digest;
        abort_unless($disk->exists($path),404);
        $mime=match(strtolower(pathinfo($filename,PATHINFO_EXTENSION))){
            'html'=>'text/html; charset=utf-8','png'=>'image/png','webp'=>'image/webp',default=>'image/jpeg'
        };
        return response($disk->get($path),200,['Content-Type'=>$mime,'Cache-Control'=>'private, no-store']);
    }

    public function release(Request $request, Publication $publication): Response
    {
        abort_unless($publication->status==='published',404);
        return response($this->renderReleaseHtml($publication->payload,app(BuildEditorialPlaylist::class)->build()),200,[
            'Content-Type'=>'text/html; charset=utf-8','Cache-Control'=>'private, no-store'
        ]);
    }

    public function media(Request $request, MediaAsset $asset, string $variant): Response
    {
        abort_unless(in_array($variant,['signage','web','thumb'],true),404);
        $path=$asset->preferredPath($variant);
        abort_unless(Storage::disk($asset->disk)->exists($path),404);
        $bytes=Storage::disk($asset->disk)->get($path);
        $mime=match(strtolower(pathinfo($path,PATHINFO_EXTENSION))){
            'png'=>'image/png','webp'=>'image/webp',default=>'image/jpeg'
        };
        return response($bytes,200,['Content-Type'=>$mime,'Cache-Control'=>'private, max-age=300']);
    }

    public function heartbeat(Request $request, string $deviceId): JsonResponse
    {
        $screen=$request->attributes->get('screen'); abort_unless($screen->device_id===$deviceId,403);
        $data=$request->validate([
            'player_version'=>'nullable|string|max:80','content_version'=>'nullable|string|max:160',
            'last_sync'=>'nullable|integer','last_error'=>'nullable|string|max:1000'
        ]);
        $screen->update([
            'player_version'=>$data['player_version']??null,'content_version'=>$data['content_version']??null,
            'last_seen_at'=>now(),'last_sync_at'=>isset($data['last_sync'])?now()->setTimestamp($data['last_sync']):null,
            'last_error'=>$data['last_error']??null
        ]);
        return response()->json(['ok'=>true,'server_time'=>now()->toIso8601String()]);
    }

    private function renderReleaseHtml(array $p, array $editorial=[]): string
    {
        $esc=fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $items=''; foreach(($p['items']??[]) as $i){
            if(!($i['available']??true)) continue;
            $items.='<div class="dish"><span>'.$esc($i['name']??'').'</span></div>';
        }
        $price=isset($p['price_cents']) && $p['price_cents']!==null ? number_format($p['price_cents']/100,2,',','.').' €' : '';
        if($items==='') { $items='<div class="dish">Pergunte-nos pelos pratos disponíveis</div>'; $price=''; }
        $slides=$editorial ?: [[
            'type'=>'brand','eyebrow'=>'O CANTINHO DA MARIANA',
            'title'=>'Comida boa. Conversas melhores.','body'=>'','duration'=>12,'media'=>null
        ]];
        $json=json_encode($slides,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);

        return '<!doctype html><html lang="pt"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>
*{box-sizing:border-box}html,body{margin:0;height:100%;background:#F7F0E4;color:#241A16;font-family:Arial,sans-serif;overflow:hidden}
.screen{height:100%;display:grid;grid-template-columns:2fr 1fr}.story{position:relative;background:#5B1715;color:#fff;overflow:hidden}
.photo{position:absolute;inset:0;background-size:cover;background-position:center;opacity:0;transition:opacity .7s}
.shade{position:absolute;inset:0;background:linear-gradient(180deg,rgba(36,26,22,.05),rgba(36,26,22,.78))}
.copy{position:absolute;z-index:2;left:5vw;right:5vw;bottom:5vw;transition:opacity .6s}
.copy small{font-size:1.2vw;letter-spacing:.15em}.copy h1{font-size:4.4vw;line-height:1.02;margin:.25em 0}.copy p{font-size:1.5vw;max-width:80%;line-height:1.35}
.today{padding:5vw 4vw;display:flex;flex-direction:column}.label{font-size:1.4vw;letter-spacing:.15em;color:#7A1E1E;font-weight:700}
.dish{font-size:2.4vw;border-bottom:1px solid #E8D6B8;padding:1.2vw 0}.price{margin-top:auto;font-size:4vw;font-weight:800;color:#245B38}
</style><main class="screen"><section class="story"><div class="photo" id="photo"></div><div class="shade"></div><div class="copy" id="copy"><small id="eyebrow"></small><h1 id="title"></h1><p id="body"></p></div></section><section class="today"><div class="label">HOJE</div>'.$items.'<div class="price">'.$esc($price).'</div></section></main>
<script>const slides='.$json.';let n=0,t;function show(){const s=slides[n%slides.length],c=document.getElementById("copy"),p=document.getElementById("photo");c.style.opacity=0;p.style.opacity=0;setTimeout(()=>{document.getElementById("eyebrow").textContent=s.eyebrow||s.type||"";document.getElementById("title").textContent=s.title||"O Cantinho da Mariana";document.getElementById("body").textContent=s.body||"";if(s.media&&s.media.local_path){p.style.backgroundImage=`url("${s.media.local_path}")`;p.style.backgroundPosition=`${(s.media.focal_x||.5)*100}% ${(s.media.focal_y||.5)*100}%`;p.style.opacity=1}else{p.style.backgroundImage="none"}c.style.opacity=1;n++;clearTimeout(t);t=setTimeout(show,(s.duration||12)*1000)},650)}show()</script></html>';
    }
}
