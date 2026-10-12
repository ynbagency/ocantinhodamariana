<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('media_assets', function(Blueprint $t){$t->id();$t->string('title')->nullable();$t->string('disk')->default('public');$t->string('path');$t->string('mime_type',100);$t->unsignedBigInteger('size_bytes')->default(0);$t->string('sha256',64);$t->decimal('focal_x',5,4)->default(.5);$t->decimal('focal_y',5,4)->default(.5);$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('editorial_contents', function(Blueprint $t){$t->id();$t->string('type',40);$t->string('title');$t->text('body')->nullable();$t->string('eyebrow')->nullable();$t->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();$t->string('cta_label')->nullable();$t->string('cta_url')->nullable();$t->unsignedSmallInteger('duration_seconds')->default(12);$t->unsignedSmallInteger('weight')->default(10);$t->boolean('active')->default(true);$t->timestamp('starts_at')->nullable();$t->timestamp('ends_at')->nullable();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();$t->index(['type','active']);});
 }
 public function down(): void {Schema::dropIfExists('editorial_contents');Schema::dropIfExists('media_assets');}
};
