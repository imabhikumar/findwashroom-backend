// database/migrations/2026_07_17_000001_create_service_units_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Service Types (e.g., Toilet, Shower, Feeding Room)
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('default_duration_minutes')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Service Units (specific services within a property)
        Schema::create('service_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Female Toilet", "Premium Shower"
            $table->text('description')->nullable();
            $table->integer('capacity')->default(1); // max concurrent users
            $table->integer('default_duration_minutes')->nullable(); // override service type default
            $table->decimal('price', 10, 2)->nullable(); // override property price
            $table->enum('pricing_model', ['fixed', 'dynamic', 'negotiable'])->default('fixed');
            $table->enum('status', ['available', 'limited', 'busy', 'cleaning', 'closed', 'emergency'])->default('available');
            $table->json('operating_hours')->nullable(); // JSON for slot-based availability
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'status']);
            $table->index(['service_type_id', 'is_active']);
        });

        // Products Catalog
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->string('size')->nullable(); // e.g., "Medium", "XL"
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->enum('availability', ['available', 'limited', 'out_of_stock'])->default('available');
            $table->string('image_url')->nullable();
            $table->json('metadata')->nullable(); // extra attributes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'availability']);
            $table->index(['category_id', 'is_active']);
        });

        // Pivot table for booking service units
        Schema::create('booking_service_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_unit_id')->constrained()->cascadeOnDelete();
            $table->integer('duration_minutes')->default(10);
            $table->decimal('price', 10, 2);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'service_unit_id']);
            $table->index(['service_unit_id', 'started_at', 'ended_at']);
        });

        // Pivot table for booking products
        Schema::create('booking_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2); // price at time of purchase
            $table->timestamps();

            $table->unique(['booking_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_products');
        Schema::dropIfExists('booking_service_units');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('service_units');
        Schema::dropIfExists('service_types');
    }
};