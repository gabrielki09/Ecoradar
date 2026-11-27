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
        Schema::create('collection_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('collection_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('scheduled_day');
            $table->time('scheduled_time_start')->nullable();
            $table->time('scheduled_time_end')->nullable();
            $table->text('observation')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_request_hours');
    }
};
