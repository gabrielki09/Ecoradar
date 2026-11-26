<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_request_id')->constrained()->restrictOnDelete();
            $table->enum('beneficiary_type', ['user', 'company']);
            $table->unsignedBigInteger('beneficiary_id');
            $table->string('reward_type');
            $table->unsignedInteger('points');
            $table->timestamps();

            $table->unique(['collection_request_id', 'beneficiary_type', 'beneficiary_id', 'reward_type'], 'reward_entries_unique_credit');
            $table->index(['beneficiary_type', 'beneficiary_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_entries');
    }
};
