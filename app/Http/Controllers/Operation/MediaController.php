<?php
namespace App\Http\Controllers\Operation;

use App\Domain\Media\GenerateImageDerivatives;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController
{
    public function index(): View
    {
        return view('operation.media', ['assets'=>MediaAsset::latest()->paginate(24)]);
    }

    public function store(Request $request, GenerateImageDerivatives $derivatives): RedirectResponse
    {
        $data=$request->validate([
            'image'=>'required|file|image|mimes:jpg,jpeg,png,webp|max:12288',
            'title'=>'nullable|string|max:120',
            'focal_x'=>'nullable|numeric|min:0|max:1',
            'focal_y'=>'nullable|numeric|min:0|max:1',
        ]);

        $file=$data['image'];
        $disk='public';
        $path=$file->store('media/originals',$disk);
        $bytes=Storage::disk($disk)->get($path);

        try {
            $variants=$derivatives->execute($disk,$path,$file->getMimeType());
            MediaAsset::create([
                'title'=>($data['title']??null) ?: pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME),
                'disk'=>$disk,'path'=>$path,
                'thumb_path'=>$variants['thumb_path'],'web_path'=>$variants['web_path'],
                'signage_path'=>$variants['signage_path'],
                'mime_type'=>$file->getMimeType(),'width'=>$variants['width'],'height'=>$variants['height'],
                'size_bytes'=>strlen($bytes),'sha256'=>hash('sha256',$bytes),
                'focal_x'=>$data['focal_x'] ?? .5,'focal_y'=>$data['focal_y'] ?? .5,
                'created_by'=>$request->user()->id,
            ]);
        } catch (\Throwable $e) {
            Storage::disk($disk)->delete($path);
            throw $e;
        }

        return back()->with('status','Fotografia adicionada à biblioteca.');
    }

    public function updateFocus(Request $request, MediaAsset $asset): RedirectResponse
    {
        $d=$request->validate(['focal_x'=>'required|numeric|min:0|max:1','focal_y'=>'required|numeric|min:0|max:1']);
        $asset->update($d);
        return back()->with('status','Ponto focal atualizado.');
    }
}
