<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {public function up():void{Schema::create('restaurant_settings',function(Blueprint $t){$t->id();$t->string('key',120)->unique();$t->text('value')->nullable();$t->boolean('public')->default(false);$t->timestamps();});} public function down():void{Schema::dropIfExists('restaurant_settings');}};
