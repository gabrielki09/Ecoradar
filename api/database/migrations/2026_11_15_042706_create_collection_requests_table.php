<?php

use App\Enum\CollectionRequest\CollectionRequestStatus;
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
        Schema::create('collection_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requester_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('city_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('cep', 8);
            $table->string('neighborhood');
            $table->string('address');

            $table->enum('status', array_column(CollectionRequestStatus::cases(), 'value'))->default(CollectionRequestStatus::OPEN->value);

            $table->foreignId('accepted_company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('accepted_collector_profile_id')
                ->nullable()
                ->constrained('collector_profiles')
                ->cascadeOnDelete();

            $table->timestamp('acceptance_date')->nullable();
            $table->timestamp('withdrawal_date')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->timestamp('canceled_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_requests');
    }
};
