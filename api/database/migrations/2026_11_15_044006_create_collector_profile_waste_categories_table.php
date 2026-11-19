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
        Schema::create('collector_profile_waste_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('collector_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('waste_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(
                ['collector_profile_id', 'waste_category_id'],
                'un_collector_profile_id_waste_category_id'
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
        Schema::dropIfExists('collector_profile_waste_categories');
    }
};
