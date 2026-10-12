<?php
namespace App\Domain\Signage;

use App\Models\EditorialContent;

class BuildEditorialPlaylist
{
    public function build(): array
    {
        $pool=EditorialContent::live()->with('media')->get()->groupBy('type');
        $targets=['food'=>5,'bulgaria'=>2,'service'=>2,'mariana'=>1,'promotion'=>1];
        $out=[];

        foreach($targets as $type=>$take){
            foreach($pool->get($type,collect())->sortByDesc('weight')->take($take) as $c){
                $out[]=[
                    'id'=>$c->id,'type'=>$c->type,'eyebrow'=>$c->eyebrow,'title'=>$c->title,
                    'body'=>$c->body,'duration'=>$c->duration_seconds,
                    'media'=>$c->media ? [
                        'id'=>$c->media->id,
                        'local_path'=>'media/'.$c->media->id.'.'.$this->extension($c->media->preferredPath('signage')),
                        'focal_x'=>$c->media->focal_x,'focal_y'=>$c->media->focal_y,
                    ] : null,
                ];
            }
        }
        return $out;
    }

    private function extension(string $path): string
    {
        return strtolower(pathinfo($path,PATHINFO_EXTENSION) ?: 'jpg');
    }
}
