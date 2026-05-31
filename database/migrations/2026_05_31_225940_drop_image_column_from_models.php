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
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('testimonies', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        Schema::table('testimonies', function (Blueprint $table) {
            $table->string('image')->nullable();
        });
    }
};
