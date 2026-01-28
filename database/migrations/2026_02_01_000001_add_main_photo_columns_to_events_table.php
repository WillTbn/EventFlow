<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('main_photo_path')->nullable()->after('capacity');
            $table->string('main_photo_medium_path')->nullable()->after('main_photo_path');
            $table->string('main_photo_thumb_path')->nullable()->after('main_photo_medium_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'main_photo_path',
                'main_photo_medium_path',
                'main_photo_thumb_path',
            ]);
        });
    }
};
