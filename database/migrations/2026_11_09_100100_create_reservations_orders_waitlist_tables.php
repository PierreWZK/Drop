<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();
            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drop_size_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('status', 20)->default('reserved'); // reserved, paid, expired
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['drop_id', 'status']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->unsignedInteger('amount_cents');
            $table->string('stripe_session_id')->nullable()->unique();
            $table->text('shipping_address')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();

            $table->unique(['drop_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('reservations');
    }
};
