<?php

use App\Enum\Collection\CollectionPointStatus;
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
        Schema::create('collection_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cep', 8);
            $table->string('neighborhood');
            $table->string('address');
            $table->text('guidelines');
            $table->string('contact', 120);
            $table->enum('responsible_operator_type', array_column(ProfileTypes::cases(), 'value'));
            $table->foreignId('responsible_operator_id');
            $table->enum('status', array_column(CollectionPointStatus::cases(), 'value'))->default(CollectionPointStatus::RECEIVING_MATERIALS->value);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_points');
    }
};
