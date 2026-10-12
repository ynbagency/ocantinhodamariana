<?php
return array_replace(require base_path('vendor/laravel/framework/config/app.php'), [
 'timezone' => env('APP_TIMEZONE', 'Europe/Lisbon'),
 'locale' => env('APP_LOCALE', 'pt_PT'),
 'fallback_locale' => env('APP_FALLBACK_LOCALE', 'pt_PT'),
]);
