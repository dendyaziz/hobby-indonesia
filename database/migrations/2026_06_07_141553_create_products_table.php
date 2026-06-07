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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('availability');
            $table->bigInteger('price');
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->bigInteger('discounted_price')->nullable();
            $table->string('brand', 50)->nullable();
            $table->string('manufacture_country', 50)->nullable();
            $table->string('publisher', 50)->nullable();
            $table->string('designer', 50)->nullable();
            $table->string('artist', 50)->nullable();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('min_player')->nullable();
            $table->unsignedTinyInteger('max_player')->nullable();
            $table->unsignedTinyInteger('playing_duration')->nullable();
            $table->string('youtube')->nullable();
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
