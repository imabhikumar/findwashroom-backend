<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'booking_number')) $table->string('booking_number')->nullable()->after('id');
            if (! Schema::hasColumn('bookings', 'customer_user_id')) $table->unsignedBigInteger('customer_user_id')->nullable()->after('customer_id');
            if (! Schema::hasColumn('bookings', 'booking_type')) $table->string('booking_type')->nullable()->after('status');
            if (! Schema::hasColumn('bookings', 'scheduled_at')) $table->timestamp('scheduled_at')->nullable();
            if (! Schema::hasColumn('bookings', 'started_at')) $table->timestamp('started_at')->nullable();
            if (! Schema::hasColumn('bookings', 'ended_at')) $table->timestamp('ended_at')->nullable();
            if (! Schema::hasColumn('bookings', 'total_amount')) $table->decimal('total_amount', 10, 2)->default(0);
        });

        DB::statement("ALTER TABLE bookings MODIFY status VARCHAR(30) NOT NULL DEFAULT 'pending'");
        DB::statement('UPDATE bookings SET customer_user_id = customer_id WHERE customer_user_id IS NULL');
        DB::statement('UPDATE bookings SET total_amount = amount WHERE total_amount = 0');
        DB::statement("UPDATE bookings SET booking_number = CONCAT('BK-', id) WHERE booking_number IS NULL");
        DB::statement("UPDATE bookings SET booking_type = 'instant' WHERE booking_type IS NULL");
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['booking_number', 'customer_user_id', 'booking_type', 'scheduled_at', 'started_at', 'ended_at', 'total_amount'] as $column) {
                if (Schema::hasColumn('bookings', $column)) $table->dropColumn($column);
            }
        });
    }
};