<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->enum('type', ['h3', 'h1']);
            $table->enum('method', ['wa', 'email'])->default('wa');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('booking_id');
            $table->index('type');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
