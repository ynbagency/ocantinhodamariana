<?php
namespace App\Http\Controllers;

use App\Domain\Publishing\PublishDailyMenu;
use App\Models\DailyMenu;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyMenuController
{
    public function public()
    {
        $menu = DailyMenu::whereDate('service_date', today())->first();
        $snapshot = $menu
            ? Publication::where('daily_menu_id', $menu->id)->where('status', 'published')->orderByDesc('version')->first()?->payload
            : null;

        return view('public.today', compact('snapshot'));
    }

    public function edit(Request $request)
    {
        abort_unless($request->user()->isOperator(), 403);
        $menu = DailyMenu::with('items')->firstOrNew(['service_date' => today()->toDateString()]);
        $publication = $menu->exists
            ? Publication::with('attempts')->where('daily_menu_id', $menu->id)->orderByDesc('version')->first()
            : null;

        $catalogue=\App\Models\MenuItem::visible()->with('category')->orderBy('name')->get();
        $media=\App\Models\MediaAsset::latest()->get();
        return view('operation.daily-menu', compact('menu', 'publication','catalogue','media'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isOperator(), 403);
        $rules=[
            'soup' => 'nullable|string|max:160',
            'dish1' => 'nullable|string|max:160',
            'dish2' => 'nullable|string|max:160',
            'price' => 'nullable|numeric|min:0|max:9999',
            'intent' => 'nullable|in:draft,publish',
            'soup_available' => 'nullable|boolean',
            'dish1_available' => 'nullable|boolean',
            'dish2_available' => 'nullable|boolean',
        ];
        foreach(['soup','dish1','dish2'] as $key){$rules[$key.'_source_id']='nullable|integer|exists:menu_items,id';$rules[$key.'_photo_id']='nullable|integer|exists:media_assets,id';}
        $data=$request->validate($rules);
        $prepared=[];
        foreach(['soup'=>'sopa','dish1'=>'prato','dish2'=>'prato'] as $key=>$category){
            $source=null;
            if(!empty($data[$key.'_source_id'])){
                $source=\App\Models\MenuItem::visible()->with('category')->find($data[$key.'_source_id']);
                if(!$source || $source->category->kind!==($key==='soup'?'soup':'dish')) throw \Illuminate\Validation\ValidationException::withMessages([$key.'_source_id'=>'Este prato não está disponível para esta seleção.']);
            }
            $name=$source?->name??($data[$key]??null);
            if(filled($name)) $prepared[]=['category'=>$category,'name'=>$name,'description'=>$source?->description,'allergens'=>$source?->allergens??[],'menu_item_id'=>$source?->id,'media_asset_id'=>array_key_exists($key.'_photo_id',$data)?$data[$key.'_photo_id']:$source?->media_asset_id,'available'=>(bool)($data[$key.'_available']??true),'position'=>count($prepared)];
        }
        if(($data['intent']??'draft')==='publish' && !collect($prepared)->contains('available',true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['publish'=>'É necessário pelo menos um prato disponível.']);
        }

        DB::transaction(function () use ($data, $request,$prepared) {
            $menu = DailyMenu::firstOrCreate(
                ['service_date' => today()->toDateString()],
                ['created_by' => $request->user()->id]
            );
            $menu=DailyMenu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            // A published snapshot stays immutable and live while the operator edits
            // the next draft. `status=draft` refers to the working copy only.
            $menu->update([
                'status' => 'draft',
                'scheduled_at' => null,
                'price_cents' => isset($data['price']) ? (int) round($data['price'] * 100) : null,
            ]);
            $menu->items()->delete();
            foreach($prepared as $item) $menu->items()->create($item);
            if(($data['intent']??'draft')==='publish') app(PublishDailyMenu::class)->execute($menu);
        });

        if(($data['intent']??'draft')==='publish') return redirect()->route('operation.edit')->with('success','Pratos guardados e publicados no site e no conteúdo da TV.');
        return redirect()->route('operation.edit')->with('success', 'Rascunho guardado. Qualquer agendamento anterior foi cancelado; a versão pública mantém-se ativa.');
    }

    public function publish(Request $request, PublishDailyMenu $publisher)
    {
        abort_unless($request->user()->isOperator(), 403);
        $menu = DailyMenu::whereDate('service_date', today())->firstOrFail();
        try {
            $publisher->execute($menu);
        } catch (\DomainException $e) {
            return back()->withErrors(['publish' => $e->getMessage()]);
        }

        return redirect()->route('operation.edit')->with('success', 'Publicado no site e na TV. Redes sociais ficam pendentes até estarem configuradas.');
    }

    public function copyYesterday(Request $request)
    {
        DB::transaction(function () use ($request) {
            $source=DailyMenu::whereDate('service_date',today()->subDay())->first();
            $snapshot=$source ? Publication::where('daily_menu_id',$source->id)->where('status','published')->latest('version')->first()?->payload : null;
            if(!$snapshot) throw \Illuminate\Validation\ValidationException::withMessages(['copy'=>'Ontem não foi publicado nenhum menu.']);
            $menu=DailyMenu::firstOrCreate(['service_date'=>today()->toDateString()],['created_by'=>$request->user()->id]);
            $menu=DailyMenu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            if($menu->items()->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['copy'=>'O rascunho de hoje já contém pratos. A cópia não o substituiu.']);
            $menu->update(['price_cents'=>$snapshot['price_cents']??null,'status'=>'draft','scheduled_at'=>null]);
            foreach($snapshot['items'] as $item) $menu->items()->create(array_replace($item,['available'=>true]));
            app(\App\Domain\Operations\Audit::class)->write('daily-menu.copied-yesterday',$menu,[], $request);
        });
        return back()->with('success','Menu de ontem copiado para o rascunho. Reveja os pratos antes de publicar.');
    }

    public function schedule(Request $request)
    {
        $data=$request->validate(['scheduled_at'=>'required|date|after:now|before:tomorrow']);
        DB::transaction(function () use ($data,$request) {
            $menu=DailyMenu::whereDate('service_date',today())->lockForUpdate()->firstOrFail();
            if(!$menu->items()->where('available',true)->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['scheduled_at'=>'Guarde pelo menos um prato disponível antes de agendar.']);
            $menu->update(['scheduled_at'=>\Illuminate\Support\Carbon::parse($data['scheduled_at'])->setTimezone(config('app.timezone')),'status'=>'scheduled']);
            app(\App\Domain\Operations\Audit::class)->write('daily-menu.scheduled',$menu,['scheduled_at'=>$menu->scheduled_at->toIso8601String()],$request);
        });
        return back()->with('success','Publicação agendada. Editar e guardar o rascunho cancela este agendamento.');
    }

    public function cancelSchedule(Request $request)
    {
        DB::transaction(function() use($request) {
            $menu=DailyMenu::whereDate('service_date',today())->whereNotNull('scheduled_at')->lockForUpdate()->first();
            if(!$menu) return;
            $menu->update(['scheduled_at'=>null,'status'=>'draft']);
            app(\App\Domain\Operations\Audit::class)->write('daily-menu.schedule-cancelled',$menu,[],$request);
        });
        return back()->with('success','Agendamento cancelado.');
    }

    public function availability(Request $request, \App\Domain\Publishing\SetPublishedAvailability $action)
    {
        $data=$request->validate(['version'=>'required|integer|min:1','position'=>'required|integer|min:0','available'=>'required|boolean']);
        $action->execute($data['version'],$data['position'],(bool)$data['available']);
        return back()->with('success','Disponibilidade atualizada no site e no conteúdo da TV.');
    }
}
