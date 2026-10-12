<?php
namespace App\Http\Controllers;

use App\Models\DailyMenu;
use App\Models\Publication;
use App\Models\Screen;
use Illuminate\Http\Request;

class OperationController
{
    public function dashboard(Request $request)
    {
        abort_unless($request->user()->isOperator(), 403);
        $menu = DailyMenu::with('items')->whereDate('service_date', today())->first();
        $publication = $menu ? Publication::with('attempts')->where('daily_menu_id',$menu->id)->latest('version')->first() : null;
        $screens = Screen::orderBy('name')->get();
        return view('operation.dashboard', compact('menu','publication','screens'));
    }

    public function preview(Request $request, string $channel)
    {
        abort_unless($request->user()->isOperator(), 403);
        abort_unless(in_array($channel,['site','tv'],true),404);
        $menu=DailyMenu::with('items')->whereDate('service_date',today())->first();
        return view('operation.preview', compact('menu','channel'));
    }
}
