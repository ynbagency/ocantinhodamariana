<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MediaAsset;
use App\Models\Publication;
use App\Models\Screen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MenuCatalogueTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void {parent::setUp();$this->actingAs(User::create(['name'=>'Operador','email'=>'operator@example.test','password'=>'test-password','role'=>'operator']));}
 private function item(array $overrides=[]):MenuItem {
  $category=MenuCategory::create(['name'=>'Pratos','kind'=>'dish','active'=>true]);
  return MenuItem::create(array_replace(['menu_category_id'=>$category->id,'name'=>'Prato da carta','description'=>'Descrição confirmada','price_cents'=>1450,'allergens'=>['milk','eggs'],'active'=>true],$overrides));
 }
 public function test_operator_can_create_edit_and_hide_catalogue_items():void {
  $this->post('/operacao/carta/categorias',['name'=>'Peixe','kind'=>'dish','position'=>2])->assertRedirect();
  $category=MenuCategory::first();
  $this->post('/operacao/carta/pratos',['menu_category_id'=>$category->id,'name'=>'Peixe de teste','price'=>'14.50','allergens'=>['fish'],'active'=>1])->assertRedirect('/operacao/carta');
  $item=MenuItem::first();$this->assertSame(1450,$item->price_cents);
  $this->get('/menu')->assertSee('Peixe de teste')->assertSee('14,50')->assertSee('Alergénios: Peixe');
  $this->get('/operacao/carta')->assertOk();$this->get('/operacao/carta/novo')->assertOk();$this->get('/operacao/carta/pratos/'.$item->id)->assertOk();
  $this->post('/operacao/carta/pratos/'.$item->id,['menu_category_id'=>$category->id,'name'=>'Peixe revisto','active'=>0])->assertRedirect();
  $this->get('/menu')->assertDontSee('Peixe revisto');$this->assertSame([],$item->fresh()->allergens);
 }
 public function test_validation_rejects_unknown_allergens_negative_prices_and_missing_media():void {
  $item=$this->item();
  $this->post('/operacao/carta/pratos',['menu_category_id'=>$item->menu_category_id,'name'=>'Inválido','price'=>-1,'allergens'=>['invented'],'media_asset_id'=>999,'active'=>1])->assertSessionHasErrors(['price','allergens.0','media_asset_id']);
  $this->assertSame(1,MenuItem::count());
 }
 public function test_hidden_category_is_excluded_from_public_menu_and_daily_selection():void {
  $item=$this->item();$item->category->update(['active'=>false]);
  $this->get('/menu')->assertDontSee('Prato da carta');
  $this->post('/operacao/pratos-de-hoje',['dish1_source_id'=>$item->id,'intent'=>'publish'])->assertSessionHasErrors('dish1_source_id');
  $this->assertSame(0,Publication::count());
 }
 public function test_daily_selection_copies_data_and_later_catalogue_edits_do_not_change_publication():void {
  $item=$this->item();
  $this->post('/operacao/pratos-de-hoje',['dish1_source_id'=>$item->id,'price'=>'12.00','intent'=>'publish'])->assertRedirect()->assertSessionHasNoErrors();
  $snapshot=Publication::first()->payload;
  $this->assertSame('Prato da carta',$snapshot['items'][0]['name']);$this->assertSame(['milk','eggs'],$snapshot['items'][0]['allergens']);
  $item->update(['name'=>'Outro nome','allergens'=>['fish'],'price_cents'=>2000]);
  $this->get('/')->assertSee('Prato da carta')->assertSee('Leite')->assertDontSee('Outro nome');
  $this->assertSame($snapshot,Publication::first()->payload);
 }
 public function test_soup_and_dish_slots_do_not_accept_incompatible_categories():void {
  $item=$this->item();
  $this->post('/operacao/pratos-de-hoje',['soup_source_id'=>$item->id])->assertSessionHasErrors('soup_source_id');
 }
 public function test_photo_is_in_public_snapshot_and_checksummed_tv_bundle():void {
  Storage::fake('public');Storage::fake('local');
  Storage::disk('public')->put('test.jpg','image-bytes');
  $media=MediaAsset::create(['disk'=>'public','path'=>'test.jpg','mime_type'=>'image/jpeg','sha256'=>hash('sha256','image-bytes'),'size_bytes'=>11]);
  $item=$this->item(['media_asset_id'=>$media->id]);
  $this->post('/operacao/pratos-de-hoje',['dish1_source_id'=>$item->id,'intent'=>'publish'])->assertSessionHasNoErrors();
  $snapshot=Publication::first()->payload;$this->assertSame($media->id,$snapshot['items'][0]['media_asset_id']);
  $this->get('/')->assertSee($snapshot['items'][0]['photo']['web_url'],false);
  $screen=Screen::create(['device_id'=>'tv-photo','name'=>'TV','enabled'=>true,'token_hash'=>hash('sha256','secret')]);
  $headers=['X-Device-ID'=>$screen->device_id,'Authorization'=>'Bearer secret'];
  $manifest=$this->getJson('/api/signage/manifest/'.$screen->device_id,$headers)->assertOk()->json();
  $asset=collect($manifest['assets'])->firstWhere('path','media/'.$media->id.'.jpg');
  $this->assertNotNull($asset);$bytes=$this->get($asset['url'],$headers)->assertOk()->getContent();
  $this->assertSame($asset['sha256'],hash('sha256',$bytes));
 }
 public function test_explicit_no_photo_overrides_catalogue_photo():void {
  $media=MediaAsset::create(['disk'=>'public','path'=>'test.jpg','mime_type'=>'image/jpeg','sha256'=>str_repeat('a',64),'size_bytes'=>1]);$item=$this->item(['media_asset_id'=>$media->id]);
  $this->post('/operacao/pratos-de-hoje',['dish1_source_id'=>$item->id,'dish1_photo_id'=>'','intent'=>'publish'])->assertSessionHasNoErrors();
  $this->assertNull(Publication::first()->payload['items'][0]['photo']);
 }
 public function test_image_upload_without_title_creates_derivatives():void {
  Storage::fake('public');
  $this->post('/operacao/fotografias',['image'=>UploadedFile::fake()->image('dish.jpg',800,600)])->assertRedirect()->assertSessionHasNoErrors();
  $asset=MediaAsset::firstOrFail();$this->assertSame('dish',$asset->title);
  foreach(['thumb','web','signage'] as $variant) Storage::disk('public')->assertExists($asset->preferredPath($variant));
 }
}
