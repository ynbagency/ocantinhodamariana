<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class RequireOperator {
 public function handle(Request $r,Closure $next):Response {
  abort_unless($r->user() && in_array($r->user()->role??'operator',['operator','admin'],true),403);
  return $next($r);
 }
}
