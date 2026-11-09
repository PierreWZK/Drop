<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->unsignedInteger('price_cents');
            $table->json('sizes');
            $table->string('image_path')->nullable(); // relatif à public/, ex. catalog/veste-chore-minuit.jpg
            $table->timestamp('drawn_at')->nullable(); // renseigné pendant le cycle en cours ; remis à null au cycle suivant
            $table->unsignedInteger('times_drawn')->default(0);
            $table->timestamps();
        });

        Schema::table('drops', function (Blueprint $table) {
            $table->foreignId('catalog_product_id')->nullable()->after('id')->constrained('catalog_products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('drops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('catalog_product_id');
        });
        Schema::dropIfExists('catalog_products');
    }
};
