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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();

            $table->integer('codigo_tom', false)->unique('un_city_codigo_tom');
            $table->integer('codigo_ibge', false)->unique('un_city_codigo_ibge');
            $table->string('municipio_tom');
            $table->string('municipio_ibge');
            $table->string('uf', 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
