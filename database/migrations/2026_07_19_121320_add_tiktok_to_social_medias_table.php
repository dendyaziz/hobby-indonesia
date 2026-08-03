<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('social_medias', 'tiktok')) {
            Schema::table('social_medias', function (Blueprint $table): void {
                $table->string('tiktok', 100)->nullable()->after('instagram');
            });
        }
    }

    public function down(): void
    {
        Schema::table('social_medias', function (Blueprint $table): void {
            $table->dropColumn('tiktok');
        });
    }
};
