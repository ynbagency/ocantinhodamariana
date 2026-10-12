<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withProviders([App\Providers\RestaurantViewServiceProvider::class])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['operator' => App\Http\Middleware\RequireOperator::class]);
        $middleware->append(App\Http\Middleware\SecurityHeaders::class);
        $middleware->preventRequestForgery(except: ['api/signage/heartbeat/*']);
    })
    ->withExceptions(fn (Exceptions $exceptions) => null)
    ->create();
