<?php
namespace Tests\Feature;
use App\Domain\Publishing\PublishDailyMenu;
use App\Models\DailyMenu;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyOperationTest extends TestCase
{
 use RefreshDatabase;
 protected function setUp(): void {
  parent::setUp();
  $this->actingAs(User::create(['name'=>'Operador','email'=>'operator@example.test','password'=>'test-password','role'=>'operator']));
 }
 private function save(string $dish='Prato original', string $intent='draft'): void {
  $this->post('/operacao/pratos-de-hoje',['dish1'=>$dish,'price'=>'12.50','intent'=>$intent])->assertRedirect();
 }
 public function test_publish_button_saves_current_fields_and_empty_publish_is_rejected(): void {
  $this->save('Prato do formulário','publish');
  $this->get('/')->assertSee('Prato do formulário');
  $this->post('/operacao/pratos-de-hoje',['intent'=>'publish'])->assertSessionHasErrors('publish');
  $this->assertSame(1,Publication::count());
 }
 public function test_yesterday_copy_is_a_draft_and_never_overwrites_existing_items(): void {
  $now=now(); $this->travelTo($now->copy()->subDay());
  $this->save('Menu de ontem','publish');
  $this->travelTo($now);
  $this->post('/operacao/pratos-de-hoje/copiar-ontem')->assertRedirect();
  $today=DailyMenu::whereDate('service_date',today())->firstOrFail();
  $this->assertSame('Menu de ontem',$today->items->first()->name);
  $this->assertSame('draft',$today->status);
  $this->get('/')->assertDontSee('Menu de ontem');
  $this->save('Rascunho próprio');
  $this->post('/operacao/pratos-de-hoje/copiar-ontem')->assertSessionHasErrors('copy');
  $this->assertSame('Rascunho próprio',$today->fresh()->items->first()->name);
 }
 public function test_missing_yesterday_reports_an_error_without_creating_today(): void {
  $this->post('/operacao/pratos-de-hoje/copiar-ontem')->assertSessionHasErrors('copy');
  $this->assertSame(0,DailyMenu::count());
 }
 public function test_availability_updates_only_published_snapshot_and_checks_version(): void {
  $this->save('Prato público','publish');
  $this->save('Rascunho privado');
  $this->post('/operacao/pratos-de-hoje/disponibilidade',['version'=>1,'position'=>0,'available'=>0])->assertRedirect();
  $menu=DailyMenu::first();
  $this->assertSame('Rascunho privado',$menu->items->first()->name);
  $this->assertTrue($menu->items->first()->available);
  $this->assertSame('draft',$menu->status);
  $this->assertFalse(Publication::latest('version')->first()->payload['items'][0]['available']);
  $this->assertTrue(Publication::where('version',1)->first()->payload['items'][0]['available']);
  $this->get('/')->assertDontSee('Prato público')->assertDontSee('Rascunho privado')->assertSee('Pergunte-nos')->assertDontSee('12,50');
  $this->get('/operacao/pratos-de-hoje')->assertOk()->assertSee('Voltar a disponibilizar');
  $this->post('/operacao/pratos-de-hoje/disponibilidade',['version'=>1,'position'=>0,'available'=>1])->assertSessionHasErrors('availability');
  $this->post('/operacao/pratos-de-hoje/disponibilidade',['version'=>2,'position'=>0,'available'=>1])->assertRedirect();
  $this->get('/')->assertSee('Prato público')->assertDontSee('Rascunho privado');
 }
 public function test_due_schedule_publishes_once_and_never_before_due_time(): void {
  $this->travelTo(now()->startOfDay()->addHours(10));
  $this->save('Prato agendado');
  $this->post('/operacao/pratos-de-hoje/agendar',['scheduled_at'=>now()->addMinutes(5)->format('Y-m-d\TH:i')])->assertRedirect()->assertSessionHasNoErrors();
  $this->artisan('cantinho:publish-scheduled')->assertSuccessful();
  $this->assertSame(0,Publication::count());
  $this->travel(6)->minutes();
  $this->artisan('cantinho:publish-scheduled')->assertSuccessful();
  $this->artisan('cantinho:publish-scheduled')->assertSuccessful();
  $this->assertSame(1,Publication::count());
  $this->assertNull(DailyMenu::first()->scheduled_at);
  $this->get('/')->assertSee('Prato agendado');
 }
 public function test_saving_a_draft_cancels_schedule_and_availability_preserves_it(): void {
  $this->travelTo(now()->startOfDay()->addHours(10));
  $this->save('Prato público','publish'); $this->save('Prato futuro');
  $due=now()->addMinutes(5);
  $this->post('/operacao/pratos-de-hoje/agendar',['scheduled_at'=>$due->format('Y-m-d\TH:i')])->assertSessionHasNoErrors();
  $this->post('/operacao/pratos-de-hoje/disponibilidade',['version'=>1,'position'=>0,'available'=>0])->assertRedirect();
  $this->assertSame('scheduled',DailyMenu::first()->status);
  $this->assertTrue(DailyMenu::first()->scheduled_at->equalTo($due->startOfMinute()));
  $this->save('Novo rascunho');
  $this->travel(6)->minutes(); $this->artisan('cantinho:publish-scheduled')->assertSuccessful();
  $this->assertSame(2,Publication::count());
  $this->assertNull(DailyMenu::first()->scheduled_at);
 }
 public function test_expired_schedule_does_not_publish_yesterdays_menu(): void {
  $this->save(); $menu=DailyMenu::first();
  $menu->update(['service_date'=>today()->subDay(),'scheduled_at'=>now()->subDay(),'status'=>'scheduled']);
  $this->artisan('cantinho:publish-scheduled')->assertSuccessful();
  $this->assertSame(0,Publication::count()); $this->assertNull($menu->fresh()->scheduled_at);
  $this->assertDatabaseHas('operational_events',['type'=>'schedule_expired']);
 }
 public function test_schedule_rejects_past_and_next_day_and_can_be_cancelled(): void {
  $this->travelTo(now()->startOfDay()->addHours(10)); $this->save();
  foreach([now()->subMinute(),today()->addDay()->addHours(10)] as $time) $this->post('/operacao/pratos-de-hoje/agendar',['scheduled_at'=>$time->format('Y-m-d\TH:i')])->assertSessionHasErrors('scheduled_at');
  $this->flushSession();
  $this->post('/operacao/pratos-de-hoje/agendar',['scheduled_at'=>now()->addHour()->format('Y-m-d\TH:i')])->assertSessionHasNoErrors();
  $this->post('/operacao/pratos-de-hoje/cancelar-agendamento')->assertRedirect();
  $this->assertNull(DailyMenu::first()->scheduled_at);
 }
}
