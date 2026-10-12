<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class SecurityHeaders {
 public function handle(Request $request, Closure $next):Response {
  $r=$next($request);
  if($request->is('operacao','operacao/*','entrar','sair')) $r->headers->set('Cache-Control','private, no-store');
  if($request->method()==='GET' && in_array($request->path(),['/','hoje','menu','o-cantinho','takeaway','contactos','fidelizacao'],true)) $r->headers->set('Cache-Control','public, max-age=0, must-revalidate');
  $r->headers->set('X-Content-Type-Options','nosniff');
  $r->headers->set('X-Frame-Options','SAMEORIGIN');
  $r->headers->set('Referrer-Policy','strict-origin-when-cross-origin');
  $r->headers->set('Permissions-Policy','camera=(), microphone=(), geolocation=()');
  if(app()->environment('production')) $r->headers->set('Strict-Transport-Security','max-age=31536000; includeSubDomains');
  return $r;
 }
}
