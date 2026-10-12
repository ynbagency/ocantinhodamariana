<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('screens', function (Blueprint $t) {
   $t->id(); $t->uuid('device_id')->unique(); $t->string('name');
   $t->string('token_hash',64); $t->boolean('enabled')->default(true);
   $t->string('player_version')->nullable(); $t->string('content_version')->nullable();
   $t->timestamp('last_seen_at')->nullable(); $t->timestamp('last_sync_at')->nullable();
   $t->text('last_error')->nullable(); $t->json('meta')->nullable(); $t->timestamps();
  });
 }
 public function down(): void { Schema::dropIfExists('screens'); }
};
