<?php

use App\Enum\CollectionRequest\CollectionRequestStatus;
use App\Enum\ProfileTypes;
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
        Schema::create('collection_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('collection_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('old_status', array_column(CollectionRequestStatus::cases(), 'value'));
            $table->enum('new_status', array_column(CollectionRequestStatus::cases(), 'value'));
            $table->enum('accepted_provider_type', array_column(ProfileTypes::cases(), 'value'));
            $table->foreignId('accepted_provider_id');

            $table->timestamp('acceptance_date')->nullable();
            $table->timestamp('withdrawal_date')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->timestamp('canceled_date')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_histories');
    }
};
