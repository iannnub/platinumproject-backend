<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('booking_code', 50)->unique();
            $table->string('bride_names');
            $table->string('initials', 10);
            $table->date('event_date');
            $table->string('event_type', 50);
            $table->enum('decoration_type', ['Dalam', 'Luar']);
            $table->string('phone', 20);
            $table->text('address');
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('maps_url', 500);
            $table->string('package_type', 100);
            $table->decimal('dp_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->enum('payment_status', ['pending', 'dp_paid', 'paid'])->default('pending');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('booking_code');
            $table->index('event_date');
            $table->index('payment_status');
            $table->index('status');
            $table->index('phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
