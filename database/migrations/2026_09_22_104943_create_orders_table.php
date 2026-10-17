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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number')->unique();
            $table->uuid('idempotency_key')->unique();
            $table->string('status')->default('new');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('delivery_method')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('customer_comment')->nullable();
            $table->text('internal_note')->nullable();
            $table->unsignedInteger('subtotal_minor');
            $table->unsignedInteger('total_minor');
            $table->string('currency', 3)->default('RUB');
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['customer_phone', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
