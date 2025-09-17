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
        Schema::create('exchangerates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->foreignId('to_currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('rate', 15, 6); // évite string pour les calculs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchangerates');
    }
};
