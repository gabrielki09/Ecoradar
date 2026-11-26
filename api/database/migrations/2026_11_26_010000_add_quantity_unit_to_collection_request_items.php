<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_request_items', function (Blueprint $table) {
            $table->enum('quantity_unit', ['UN', 'QDE', 'L', 'ML', 'KG', 'G', 'T', 'M3'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('collection_request_items', function (Blueprint $table) {
            $table->dropColumn('quantity_unit');
        });
    }
};
