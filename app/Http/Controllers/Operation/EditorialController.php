<?php
namespace App\Http\Controllers\Operation;

use App\Models\EditorialContent;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditorialController
{
    public function index(): View
    {
        return view('operation.editorial',[
            'items'=>EditorialContent::with('media')->latest()->get(),
            'media'=>MediaAsset::latest()->limit(60)->get(),
        ]);
    }

    public function store(Request $r): RedirectResponse
    {
        $d=$r->validate([
            'type'=>'required|in:food,bulgaria,mariana,service,promotion',
            'title'=>'required|string|max:120','eyebrow'=>'nullable|string|max:60',
            'body'=>'nullable|string|max:600','media_asset_id'=>'nullable|exists:media_assets,id',
            'duration_seconds'=>'required|integer|min:8|max:30','weight'=>'required|integer|min:1|max:100',
            'starts_at'=>'nullable|date','ends_at'=>'nullable|date|after_or_equal:starts_at'
        ]);
        $d['active']=$r->boolean('active'); $d['created_by']=$r->user()->id;
        EditorialContent::create($d);
        return back()->with('status','Conteúdo guardado.');
    }

    public function toggle(EditorialContent $content): RedirectResponse
    {
        $content->update(['active'=>!$content->active]);
        return back();
    }
}
