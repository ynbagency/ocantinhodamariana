<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\DailyMenu;
use App\Models\OperationalEvent;
use App\Models\Screen;
use App\Jobs\EvaluateScreenHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackofficeTest extends TestCase
{
 use RefreshDatabase;
 public function test_first_admin_credentials_are_private_and_existing_accounts_are_preserved(): void {
  Storage::fake('local');
  $this->artisan('cantinho:create-user',['email'=>'admin@example.test','--name'=>'Mariana','--admin'=>true,'--generate'=>true])->assertSuccessful();
  $user=User::where('email','admin@example.test')->firstOrFail();
  $this->assertSame('admin',$user->role);
  $file=Storage::disk('local')->get('bootstrap/login-'.hash('sha256',$user->email).'.txt');
  preg_match('/Palavra-passe: ([^\n]+)/',$file,$match);
  $this->assertTrue(Hash::check($match[1],$user->password));
  $this->post('/entrar',['email'=>$user->email,'password'=>$match[1]])->assertRedirect('/operacao');
  $this->artisan('cantinho:create-user',['email'=>$user->email,'--generate'=>true])->assertFailed();
  $this->assertSame($user->password,$user->fresh()->password);
 }
 public function test_invalid_email_does_not_create_an_account(): void {
  $this->artisan('cantinho:create-user',['email'=>'not-an-email','--generate'=>true])->assertFailed();
  $this->assertSame(0,User::count());
 }
 public function test_every_backoffice_page_has_complete_navigation_and_logout(): void {
  $this->actingAs(User::create(['name'=>'Mariana','email'=>'mariana@example.test','password'=>'test-password','role'=>'admin']));
  foreach(['/operacao','/operacao/pratos-de-hoje','/operacao/conteudos','/operacao/fotografias','/operacao/fidelizacao','/operacao/definicoes','/operacao/preview/tv'] as $path) {
   $response=$this->get($path)->assertOk()->assertSee('aria-label="Operação"',false)->assertSee('Sair');
   foreach(['dashboard','edit','editorial','media','loyalty','settings'] as $route) $response->assertSee(route('operation.'.$route),false);
  }
 }
 public function test_dashboard_shows_schedule_and_safe_actionable_alerts_only(): void {
  $this->actingAs(User::create(['name'=>'Mariana','email'=>'mariana@example.test','password'=>'test-password','role'=>'admin']));
  DailyMenu::create(['service_date'=>today(),'status'=>'scheduled','scheduled_at'=>today()->addHours(18)]);
  OperationalEvent::create(['type'=>'schedule_failed','severity'=>'warning','message'=>'SECRET-RAW-ERROR','context'=>['token'=>'SECRET-TOKEN']]);
  OperationalEvent::create(['type'=>'screen_offline','severity'=>'critical','message'=>'RESOLVED-ERROR','resolved_at'=>now()]);
  $this->get('/operacao')->assertOk()->assertSee('Publicação agendada')->assertSee('18:00')->assertSee('Uma publicação agendada não foi concluída')->assertDontSee('SECRET-RAW-ERROR')->assertDontSee('SECRET-TOKEN')->assertDontSee('RESOLVED-ERROR');
 }
 public function test_recovered_screen_clears_old_offline_alert(): void {
  $screen=Screen::create(['device_id'=>'tv-test','name'=>'TV','token_hash'=>hash('sha256','test'),'enabled'=>true,'last_seen_at'=>now()->subMinutes(15)]);
  (new EvaluateScreenHealth)->handle();
  $this->assertSame(1,OperationalEvent::whereNull('resolved_at')->count());
  $screen->update(['last_seen_at'=>now()]); (new EvaluateScreenHealth)->handle();
  $this->assertSame(0,OperationalEvent::whereNull('resolved_at')->count());
 }
}
