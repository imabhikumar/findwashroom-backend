<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_order_id')->nullable()->after('payment_gateway');
            $table->string('gateway_payment_id')->nullable()->after('gateway_order_id');
            $table->text('gateway_signature')->nullable()->after('gateway_payment_id');

            $table->unique('gateway_order_id', 'payments_gateway_order_id_unique');
            $table->unique('gateway_payment_id', 'payments_gateway_payment_id_unique');
            $table->index(['booking_id', 'status'], 'payments_booking_id_status_index');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unique('booking_id', 'reviews_booking_id_unique');
            $table->index(['property_id', 'rating'], 'reviews_property_id_rating_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['customer_id', 'property_id', 'status'], 'bookings_customer_property_status_index');
            $table->index(['property_id', 'status'], 'bookings_property_status_index');
        });

        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->index(['status', 'assigned_cleaner_id'], 'cleaning_jobs_status_cleaner_index');
            $table->index(['owner_id', 'status'], 'cleaning_jobs_owner_status_index');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->index(['booking_id', 'raised_by', 'status'], 'complaints_booking_user_status_index');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->index(['owner_id', 'is_active'], 'properties_owner_active_index');
            $table->index(['city', 'is_active'], 'properties_city_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_city_active_index');
            $table->dropIndex('properties_owner_active_index');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('complaints_booking_user_status_index');
        });

        Schema::table('cleaning_jobs', function (Blueprint $table) {
            $table->dropIndex('cleaning_jobs_owner_status_index');
            $table->dropIndex('cleaning_jobs_status_cleaner_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_property_status_index');
            $table->dropIndex('bookings_customer_property_status_index');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_property_id_rating_index');
            $table->dropUnique('reviews_booking_id_unique');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_booking_id_status_index');
            $table->dropUnique('payments_gateway_payment_id_unique');
            $table->dropUnique('payments_gateway_order_id_unique');

            $table->dropColumn(['gateway_order_id', 'gateway_payment_id', 'gateway_signature']);
        });
    }
};