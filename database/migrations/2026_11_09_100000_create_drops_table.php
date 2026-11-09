<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drops', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('price_cents');
            $table->string('image_path')->nullable();
            $table->timestamp('opens_at');
            $table->timestamp('closes_at');
            $table->unsignedSmallInteger('reservation_minutes')->default(10);
            $table->timestamps();
        });

        Schema::create('drop_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->string('label', 20);
            $table->unsignedInteger('stock');
            $table->unsignedInteger('reserved')->default(0);
            $table->unsignedInteger('sold')->default(0);
            $table->timestamps();

            $table->unique(['drop_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drop_sizes');
        Schema::dropIfExists('drops');
    }
};
