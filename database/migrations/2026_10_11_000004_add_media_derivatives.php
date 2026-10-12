<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('media_assets', function (Blueprint $t) {
            $t->string('thumb_path')->nullable()->after('path');
            $t->string('web_path')->nullable()->after('thumb_path');
            $t->string('signage_path')->nullable()->after('web_path');
            $t->unsignedInteger('width')->nullable()->after('mime_type');
            $t->unsignedInteger('height')->nullable()->after('width');
        });
    }
    public function down(): void {
        Schema::table('media_assets', function (Blueprint $t) {
            $t->dropColumn(['thumb_path','web_path','signage_path','width','height']);
        });
    }
};
