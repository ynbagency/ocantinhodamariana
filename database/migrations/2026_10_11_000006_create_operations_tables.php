<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('audit_logs',function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('action',120);$t->string('subject_type')->nullable();$t->unsignedBigInteger('subject_id')->nullable();$t->string('ip',64)->nullable();$t->json('context')->nullable();$t->timestamps();$t->index(['subject_type','subject_id']);});
  Schema::create('operational_events',function(Blueprint $t){$t->id();$t->string('type',80);$t->enum('severity',['info','warning','critical'])->default('info');$t->string('dedupe_key',190)->nullable()->index();$t->text('message');$t->json('context')->nullable();$t->timestamp('resolved_at')->nullable();$t->timestamps();});
 }
 public function down():void {Schema::dropIfExists('operational_events');Schema::dropIfExists('audit_logs');}
};
