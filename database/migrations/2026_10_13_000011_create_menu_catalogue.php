<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('menu_categories',function(Blueprint $t){$t->id();$t->string('name',80);$t->string('kind',20)->default('dish');$t->unsignedInteger('position')->default(0);$t->boolean('active')->default(true);$t->timestamps();});
  Schema::create('menu_items',function(Blueprint $t){$t->id();$t->foreignId('menu_category_id')->constrained();$t->string('name',160);$t->text('description')->nullable();$t->unsignedInteger('price_cents')->nullable();$t->json('allergens')->nullable();$t->foreignId('media_asset_id')->nullable()->constrained()->nullOnDelete();$t->boolean('active')->default(true);$t->timestamps();});
  Schema::table('daily_menu_items',function(Blueprint $t){$t->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('media_asset_id')->nullable()->constrained()->nullOnDelete();$t->json('allergens')->nullable();});
 }
 public function down():void {Schema::table('daily_menu_items',function(Blueprint $t){$t->dropConstrainedForeignId('menu_item_id');$t->dropConstrainedForeignId('media_asset_id');$t->dropColumn('allergens');});Schema::dropIfExists('menu_items');Schema::dropIfExists('menu_categories');}
};
