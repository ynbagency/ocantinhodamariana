<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('sessions', function (Blueprint $t) {
   $t->string('id')->primary(); $t->foreignId('user_id')->nullable()->index();
   $t->string('ip_address',45)->nullable(); $t->text('user_agent')->nullable();
   $t->longText('payload'); $t->integer('last_activity')->index();
  });
  Schema::create('failed_jobs', function (Blueprint $t) {
   $t->id(); $t->string('uuid')->unique(); $t->text('connection'); $t->text('queue');
   $t->longText('payload'); $t->longText('exception'); $t->timestamp('failed_at')->useCurrent();
  });
 }
 public function down(): void { Schema::dropIfExists('failed_jobs'); Schema::dropIfExists('sessions'); }
};
