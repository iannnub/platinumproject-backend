<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->enum('payment_type', ['dp', 'pelunasan', 'lainnya'])->default('dp');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['transfer', 'cash', 'ewallet'])->default('transfer');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('booking_id');
            $table->index('payment_date');
            $table->index('payment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
