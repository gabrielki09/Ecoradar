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
        Schema::create('collection_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('collection_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('waste_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('description')->nullable();
            $table->float('approximate_quantity', 12,2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_request_items');
    }
};
