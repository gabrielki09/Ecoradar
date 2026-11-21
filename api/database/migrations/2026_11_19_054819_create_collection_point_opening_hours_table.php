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
        Schema::create('collection_point_opening_hours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('collection_point_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('opening_day')->unique('un_opening_day_collection_point_opening_hours');

            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_point_opening_hours');
    }
};
