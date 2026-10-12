<?php
namespace App\Http\Middleware;
use App\Models\Screen;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class AuthenticateSignageDevice {
 public function handle(Request $request, Closure $next): Response {
  $deviceId=(string)$request->header('X-Device-ID');
  $bearer=(string)$request->bearerToken();
  $screen=Screen::where('device_id',$deviceId)->where('enabled',true)->first();
  if (!$screen || !$bearer || !hash_equals($screen->token_hash, hash('sha256',$bearer))) abort(401);
  $request->attributes->set('screen',$screen);
  return $next($request);
 }
}
