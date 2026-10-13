<?php
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\SignageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DailyMenuController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\Operation\EditorialController;
use App\Http\Controllers\Operation\MediaController;
use App\Http\Controllers\Operation\LoyaltyController;
use App\Http\Controllers\Operation\RestaurantSettingsController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Middleware\AuthenticateSignageDevice;
use Illuminate\Support\Facades\Route;

Route::get('/health',HealthController::class)->name('health');
Route::get('/',[PublicSiteController::class,'home'])->name('home');
Route::get('/hoje',[PublicSiteController::class,'home'])->name('today');

Route::middleware('guest')->group(function(){
    Route::get('/entrar',[LoginController::class,'create'])->name('login');
    Route::post('/entrar',[LoginController::class,'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::post('/sair',[LoginController::class,'destroy'])->middleware('auth')->name('logout');

Route::middleware(AuthenticateSignageDevice::class)->group(function(){
    Route::get('/api/signage/bundle/{digest}/{filename}',[SignageController::class,'bundle'])->name('signage.bundle');
    Route::get('/api/signage/manifest/{deviceId}',[SignageController::class,'manifest'])->name('signage.manifest');
    Route::get('/api/signage/release/{publication}',[SignageController::class,'release'])->name('signage.release');
    Route::post('/api/signage/heartbeat/{deviceId}',[SignageController::class,'heartbeat'])->name('signage.heartbeat');
    Route::get('/api/signage/media/{asset}/{variant}',[SignageController::class,'media'])->name('signage.media');
});

Route::get('/menu',[PublicSiteController::class,'menu'])->name('public.menu');
Route::get('/o-cantinho',[PublicSiteController::class,'story'])->name('public.story');
Route::get('/takeaway',[PublicSiteController::class,'takeaway'])->name('public.takeaway');
Route::get('/contactos',[PublicSiteController::class,'contacts'])->name('public.contacts');
Route::get('/fidelizacao',[PublicSiteController::class,'loyalty'])->name('public.loyalty');

Route::middleware(['auth','operator'])->prefix('operacao')->name('operation.')->group(function(){
    Route::get('/definicoes',[RestaurantSettingsController::class,'edit'])->name('settings');
    Route::post('/definicoes',[RestaurantSettingsController::class,'update'])->name('settings.update');
    Route::get('/health',HealthController::class)->name('health');
Route::get('/',[OperationController::class,'dashboard'])->name('dashboard');
    Route::get('/preview/{channel}',[OperationController::class,'preview'])->name('preview');
    Route::get('/pratos-de-hoje',[DailyMenuController::class,'edit'])->name('edit');
    Route::post('/pratos-de-hoje',[DailyMenuController::class,'store'])->name('store');
    Route::post('/pratos-de-hoje/publicar',[DailyMenuController::class,'publish'])->name('publish');
    Route::post('/pratos-de-hoje/copiar-ontem',[DailyMenuController::class,'copyYesterday'])->name('copy-yesterday');
    Route::post('/pratos-de-hoje/agendar',[DailyMenuController::class,'schedule'])->name('schedule');
    Route::post('/pratos-de-hoje/cancelar-agendamento',[DailyMenuController::class,'cancelSchedule'])->name('cancel-schedule');
    Route::post('/pratos-de-hoje/disponibilidade',[DailyMenuController::class,'availability'])->name('availability');
    Route::get('/conteudos',[EditorialController::class,'index'])->name('editorial');
    Route::post('/conteudos',[EditorialController::class,'store'])->name('editorial.store');
    Route::post('/conteudos/{content}/toggle',[EditorialController::class,'toggle'])->name('editorial.toggle');
    Route::get('/fidelizacao',[LoyaltyController::class,'index'])->name('loyalty');
    Route::post('/fidelizacao/clientes',[LoyaltyController::class,'customer'])->name('loyalty.customer');
    Route::post('/fidelizacao/contas/{account}/ganhar',[LoyaltyController::class,'earn'])->name('loyalty.earn');
    Route::post('/fidelizacao/recompensas',[LoyaltyController::class,'reward'])->name('loyalty.reward');
    Route::get('/fotografias',[MediaController::class,'index'])->name('media');
    Route::post('/fotografias',[MediaController::class,'store'])->name('media.store');
    Route::post('/fotografias/{asset}/foco',[MediaController::class,'updateFocus'])->name('media.focus');
});
