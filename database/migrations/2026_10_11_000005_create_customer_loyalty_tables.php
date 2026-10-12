<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('customers',function(Blueprint $t){$t->id();$t->string('name',120)->nullable();$t->string('email')->nullable()->unique();$t->string('phone',40)->nullable()->unique();$t->timestamp('consented_at')->nullable();$t->timestamps();});
  Schema::create('loyalty_accounts',function(Blueprint $t){$t->id();$t->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();$t->boolean('active')->default(true);$t->timestamps();});
  Schema::create('loyalty_transactions',function(Blueprint $t){$t->id();$t->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();$t->enum('type',['earn','redeem','reverse','adjust']);$t->integer('units');$t->string('reference',120)->nullable();$t->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();$t->json('metadata')->nullable();$t->timestamps();$t->index(['loyalty_account_id','created_at']);});
  Schema::create('rewards',function(Blueprint $t){$t->id();$t->string('name',120);$t->unsignedInteger('cost_units');$t->boolean('active')->default(false);$t->timestamps();});
  Schema::create('reward_redemptions',function(Blueprint $t){$t->id();$t->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();$t->foreignId('reward_id')->constrained();$t->foreignId('loyalty_transaction_id')->constrained();$t->enum('status',['issued','used','cancelled'])->default('issued');$t->string('code_hash',64)->unique();$t->timestamp('expires_at')->nullable();$t->timestamp('used_at')->nullable();$t->timestamps();});
 }
 public function down():void {Schema::dropIfExists('reward_redemptions');Schema::dropIfExists('rewards');Schema::dropIfExists('loyalty_transactions');Schema::dropIfExists('loyalty_accounts');Schema::dropIfExists('customers');}
};
