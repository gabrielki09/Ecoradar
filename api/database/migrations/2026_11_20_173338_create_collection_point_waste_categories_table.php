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
        Schema::create('collection_point_waste_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_point_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('waste_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(
                ['collection_point_id', 'waste_category_id'],
                'un_collection_point_id_waste_category_id'
            );

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_point_waste_categories');
    }
};
