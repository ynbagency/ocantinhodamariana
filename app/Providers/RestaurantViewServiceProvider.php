<?php
namespace App\Providers; use App\Domain\Restaurant\RestaurantFacts; use Illuminate\Support\Facades\View; use Illuminate\Support\ServiceProvider;
class RestaurantViewServiceProvider extends ServiceProvider {public function boot():void{View::composer('public.*',function($view){$view->with('restaurantFacts',app(RestaurantFacts::class)->all());});}}
