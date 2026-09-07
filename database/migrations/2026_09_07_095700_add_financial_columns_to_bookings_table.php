<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'total_paid')) {
                $table->decimal('total_paid', 15, 2)->default(0.00)->after('total_amount');
            }

            if (!Schema::hasColumn('bookings', 'remaining_amount')) {
                $table->decimal('remaining_amount', 15, 2)->nullable()->after('total_paid');
            }
        });

        // Update payment_status enum to include 'fully_paid' (MySQL/MariaDB only)
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending','dp_paid','paid','fully_paid') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumnIfExists('total_paid');
            $table->dropColumnIfExists('remaining_amount');
        });

        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending','dp_paid','paid') NOT NULL DEFAULT 'pending'");
        }
    }
};
