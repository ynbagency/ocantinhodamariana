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

        return view('operation.daily-menu', compact('menu', 'publication'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isOperator(), 403);
        $data = $request->validate([
            'soup' => 'nullable|string|max:160',
            'dish1' => 'nullable|string|max:160',
            'dish2' => 'nullable|string|max:160',
            'price' => 'nullable|numeric|min:0|max:9999',
        ]);

        DB::transaction(function () use ($data, $request) {
            $menu = DailyMenu::firstOrCreate(
                ['service_date' => today()->toDateString()],
                ['created_by' => $request->user()->id]
            );
            // A published snapshot stays immutable and live while the operator edits
            // the next draft. `status=draft` refers to the working copy only.
            $menu->update([
                'status' => 'draft',
                'price_cents' => isset($data['price']) ? (int) round($data['price'] * 100) : null,
            ]);
            $menu->items()->delete();
            $position = 0;
            foreach (['soup' => 'sopa', 'dish1' => 'prato', 'dish2' => 'prato'] as $key => $category) {
                if (filled($data[$key] ?? null)) {
                    $menu->items()->create([
                        'category' => $category,
                        'name' => $data[$key],
                        'position' => $position++,
                        'available' => true,
                    ]);
                }
            }
        });

        return redirect()->route('operation.edit')->with('success', 'Rascunho guardado. A versão pública anterior mantém-se ativa.');
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
}
