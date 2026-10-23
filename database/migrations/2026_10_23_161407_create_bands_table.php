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
        Schema::create('bands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('gender', 80)->index();
            $table->timestamps();
        });

        Schema::create('band_creation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('band_id')->unique()->constrained('bands')->cascadeOnDelete();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('band_creation_logs');
        Schema::dropIfExists('bands');
    }
};
