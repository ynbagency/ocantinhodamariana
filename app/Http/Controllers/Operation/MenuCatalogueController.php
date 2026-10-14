<?php
namespace App\Http\Controllers\Operation;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MediaAsset;
use App\Domain\Restaurant\Allergens;
use App\Domain\Operations\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuCatalogueController {
 public function index(){return view('operation.catalogue',['categories'=>MenuCategory::orderBy('position')->orderBy('name')->get(),'items'=>MenuItem::with('category','media')->orderBy('name')->paginate(30)]);}
 public function category(Request $r,Audit $audit){$data=$r->validate(['name'=>'required|string|max:80','kind'=>'required|in:soup,dish,other','position'=>'required|integer|min:0|max:9999']);$category=MenuCategory::create($data+['active'=>true]);$audit->write('menu.category.created',$category,[],$r);return back()->with('status','Categoria criada.');}
 public function updateCategory(Request $r,MenuCategory $category,Audit $audit){$data=$r->validate(['name'=>'required|string|max:80','kind'=>'required|in:soup,dish,other','position'=>'required|integer|min:0|max:9999','active'=>'required|boolean']);$category->update($data);$audit->write('menu.category.updated',$category,[],$r);return back()->with('status','Categoria atualizada.');}
 public function create(){return $this->form(new MenuItem(['active'=>true]));}
 public function edit(MenuItem $item){return $this->form($item);}
 private function form(MenuItem $item){return view('operation.catalogue-item',['item'=>$item,'categories'=>MenuCategory::orderBy('position')->orderBy('name')->get(),'media'=>MediaAsset::latest()->get(),'allergenLabels'=>Allergens::LABELS]);}
 public function store(Request $r,Audit $audit){$item=MenuItem::create($this->data($r));$audit->write('menu.item.created',$item,[],$r);return redirect()->route('operation.catalogue')->with('status','Prato criado na carta.');}
 public function update(Request $r,MenuItem $item,Audit $audit){$item->update($this->data($r));$audit->write('menu.item.updated',$item,[],$r);return redirect()->route('operation.catalogue')->with('status','Prato atualizado. Os menus diários já publicados mantêm-se iguais.');}
 private function data(Request $r):array{
  $data=$r->validate(['menu_category_id'=>'required|exists:menu_categories,id','name'=>'required|string|max:160','description'=>'nullable|string|max:1000','price'=>'nullable|numeric|min:0|max:9999','allergens'=>'nullable|array','allergens.*'=>['string','distinct',Rule::in(array_keys(Allergens::LABELS))],'media_asset_id'=>'nullable|exists:media_assets,id','active'=>'required|boolean']);
  $data['price_cents']=isset($data['price'])?(int)round($data['price']*100):null;unset($data['price']);$data['allergens']=$data['allergens']??[];return $data;
 }
}
