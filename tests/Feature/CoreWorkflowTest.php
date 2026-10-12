<?php
namespace Tests\Feature;

use App\Domain\Publishing\PublishDailyMenu;
use App\Models\DailyMenu;
use App\Models\EditorialContent;
use App\Models\RestaurantSetting;
use App\Models\Screen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoreWorkflowTest extends TestCase
{
 use RefreshDatabase;
 private function operator(string $role='operator'): User {
  return User::create(['name'=>'Operador','email'=>uniqid().'@example.test','password'=>'test-password','role'=>$role]);
 }
 private function publication() {
  $menu=DailyMenu::create(['service_date'=>today(),'price_cents'=>1250]);
  $menu->items()->create(['category'=>'prato','name'=>'Prato publicado','position'=>0,'available'=>true]);
  return app(PublishDailyMenu::class)->execute($menu);
 }
 private function device(): array {
  $screen=Screen::create(['device_id'=>'d8fe60d9-fdbe-4a74-b931-879d0f732705','name'=>'TV','token_hash'=>hash('sha256','device-secret'),'enabled'=>true]);
  return [$screen,['Authorization'=>'Bearer device-secret','X-Device-ID'=>$screen->device_id]];
 }
 public function test_public_pages_render_and_use_confirmed_facts(): void {
  RestaurantSetting::create(['key'=>'phone','value'=>'239000000','public'=>true]);
  foreach(['/','/hoje','/menu','/o-cantinho','/takeaway','/contactos','/fidelizacao','/entrar'] as $url) $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options','nosniff');
  $this->get('/contactos')->assertSee('239000000');
  $this->get('/menu')->assertHeader('Cache-Control','max-age=0, must-revalidate, public');
 }
 public function test_login_operator_permissions_and_logout(): void {
  $user=$this->operator();
  $this->get('/operacao')->assertRedirect('/entrar');
  $this->post('/entrar',['email'=>$user->email,'password'=>'test-password'])->assertRedirect('/operacao');
  $this->get('/operacao')->assertOk();
  $this->get('/operacao')->assertHeader('Cache-Control','no-store, private');
  $this->post('/sair')->assertRedirect('/entrar');
  $this->actingAs($this->operator('customer'))->get('/operacao/conteudos')->assertForbidden();
 }
 public function test_draft_edit_does_not_change_public_snapshot(): void {
  $this->publication();
  $this->actingAs($this->operator())->post('/operacao/pratos-de-hoje',['dish1'=>'Novo rascunho','price'=>'14.00'])->assertRedirect();
  $this->get('/')->assertSee('Prato publicado')->assertDontSee('Novo rascunho');
  $this->get('/operacao')->assertSee('Prato publicado')->assertDontSee('Novo rascunho');
  $this->post('/operacao/pratos-de-hoje/publicar')->assertRedirect();
  $this->get('/')->assertSee('Novo rascunho')->assertDontSee('Prato publicado');
 }
 public function test_editorial_change_creates_new_bundle_and_old_download_stays_immutable(): void {
  Storage::fake('local');
  $this->publication(); [$screen,$headers]=$this->device();
  $content=EditorialContent::create(['type'=>'food','title'=>'História original','active'=>true,'weight'=>10,'duration_seconds'=>12]);
  $url='/api/signage/manifest/'.$screen->device_id;
  $first=$this->getJson($url,$headers)->assertOk()->json();
  $asset=collect($first['assets'])->firstWhere('path','index.html');
  $old=$this->get($asset['url'],$headers)->assertOk()->getContent();
  $this->assertSame($asset['sha256'],hash('sha256',$old));
  $content->update(['title'=>'História alterada']);
  $second=$this->getJson($url,$headers)->assertOk()->json();
  $this->assertNotSame($first['version'],$second['version']);
  $this->assertSame($old,$this->get($asset['url'],$headers)->assertOk()->getContent());
  $this->assertSame($second['version'],$this->getJson($url,$headers)->json('version'));
 }
 public function test_device_authentication_and_heartbeat(): void {
  [$screen,$headers]=$this->device();
  $this->getJson('/api/signage/manifest/'.$screen->device_id)->assertUnauthorized();
  $this->getJson('/api/signage/manifest/other',$headers)->assertForbidden();
  $this->postJson('/api/signage/heartbeat/'.$screen->device_id,['content_version'=>'bundle-test','last_sync'=>time()],$headers)->assertOk();
  $this->assertSame('bundle-test',$screen->fresh()->content_version);
 }
 public function test_operator_pages_render(): void {
  $this->actingAs($this->operator());
  foreach(['/operacao/pratos-de-hoje','/operacao/conteudos','/operacao/fotografias','/operacao/fidelizacao','/operacao/definicoes','/operacao/preview/site','/operacao/preview/tv'] as $url) $this->get($url)->assertOk();
 }
 public function test_heartbeat_is_csrf_exempt_but_device_auth_remains_required(): void {
  [$screen,$headers]=$this->device();
  $this->app->instance('env','local');
  try {
   $this->postJson('/api/signage/heartbeat/'.$screen->device_id,[], $headers)->assertOk();
   $this->postJson('/api/signage/heartbeat/'.$screen->device_id,[])->assertUnauthorized();
   $this->post('/entrar',['email'=>'nobody@example.test','password'=>'x'])->assertStatus(419);
  } finally { $this->app->instance('env','testing'); }
 }
}
