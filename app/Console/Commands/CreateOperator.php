<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CreateOperator extends Command
{
 protected $signature='cantinho:create-user {email} {--name=Operador} {--admin : Create an administrator} {--generate : Generate a password and save it privately}';
 protected $description='Create a backoffice account without exposing its password in command arguments.';

 public function handle(): int {
  $email=strtolower(trim($this->argument('email')));
  $name=trim($this->option('name'));
  $validation=Validator::make(compact('email','name'),['email'=>'required|email|max:255|unique:users,email','name'=>'required|string|max:120']);
  if($validation->fails()) { foreach($validation->errors()->all() as $error) $this->error($error); return self::FAILURE; }
  $password=$this->option('generate') ? bin2hex(random_bytes(16)) : $this->secret('Palavra-passe (mínimo 12 caracteres)');
  if(!is_string($password)||strlen($password)<12) { $this->error('A palavra-passe tem de ter pelo menos 12 caracteres.'); return self::FAILURE; }
  if(!$this->option('generate') && $password!==$this->secret('Repita a palavra-passe')) { $this->error('As palavras-passe não coincidem.'); return self::FAILURE; }
  $path='bootstrap/login-'.hash('sha256',$email).'.txt';
  $disk=Storage::disk('local');
  if($this->option('generate') && $disk->exists($path)) { $this->error('Já existe um ficheiro de credenciais para este email. Não foi substituído.'); return self::FAILURE; }
  DB::transaction(function() use($email,$name,$password,$path,$disk) {
   User::create(['email'=>$email,'name'=>$name,'password'=>$password,'role'=>$this->option('admin')?'admin':'operator']);
   if($this->option('generate')) {
    if(!$disk->put($path,"Email: {$email}\nPalavra-passe: {$password}\nEntrada: ".url('/entrar')."\n")) throw new \RuntimeException('Não foi possível guardar as credenciais. A conta não foi criada.');
    @chmod($disk->path($path),0600);
   }
  });
  $this->info('Conta criada para '.$email.'.');
  if($this->option('generate')) $this->line('Credenciais privadas: '.$disk->path($path));
  return self::SUCCESS;
 }
}
