<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('email')->unique();$t->timestamp('email_verified_at')->nullable();$t->string('password');$t->string('role')->default('operator');$t->rememberToken();$t->timestamps();});
  Schema::create('daily_menus',function(Blueprint $t){$t->id();$t->date('service_date')->unique();$t->string('status')->default('draft');$t->unsignedInteger('price_cents')->nullable();$t->unsignedInteger('version')->default(0);$t->timestamp('published_at')->nullable();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('daily_menu_items',function(Blueprint $t){$t->id();$t->foreignId('daily_menu_id')->constrained()->cascadeOnDelete();$t->string('category');$t->string('name');$t->text('description')->nullable();$t->unsignedSmallInteger('position');$t->boolean('available')->default(true);$t->timestamps();});
  Schema::create('publications',function(Blueprint $t){$t->id();$t->foreignId('daily_menu_id')->constrained()->cascadeOnDelete();$t->unsignedInteger('version');$t->json('payload');$t->string('status')->default('pending');$t->timestamps();$t->unique(['daily_menu_id','version']);});
  Schema::create('publication_attempts',function(Blueprint $t){$t->id();$t->foreignId('publication_id')->constrained()->cascadeOnDelete();$t->string('channel');$t->string('status')->default('pending');$t->text('error')->nullable();$t->timestamp('completed_at')->nullable();$t->timestamps();$t->unique(['publication_id','channel']);});
 }
 public function down(): void {foreach(['publication_attempts','publications','daily_menu_items','daily_menus','users'] as $name) Schema::dropIfExists($name);}
};
