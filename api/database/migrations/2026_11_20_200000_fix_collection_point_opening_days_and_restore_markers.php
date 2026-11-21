<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_point_opening_hours', function (Blueprint $table) {
            $table->dropUnique('un_opening_day_collection_point_opening_hours');
            $table->unique(['collection_point_id', 'opening_day'], 'un_collection_point_opening_day');
            $table->boolean('deleted_with_point')->default(false);
        });

        Schema::table('collection_point_waste_categories', function (Blueprint $table) {
            $table->boolean('deleted_with_point')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('collection_point_opening_hours', function (Blueprint $table) {
            $table->dropUnique('un_collection_point_opening_day');
            $table->unique('opening_day', 'un_opening_day_collection_point_opening_hours');
            $table->dropColumn('deleted_with_point');
        });
        Schema::table('collection_point_waste_categories', function (Blueprint $table) {
            $table->dropColumn('deleted_with_point');
        });
    }
};
