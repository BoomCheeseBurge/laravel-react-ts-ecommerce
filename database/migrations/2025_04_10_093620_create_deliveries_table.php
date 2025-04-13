<?php

use App\Enums\DeliveryStatusEnum;
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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to link the delivery to the order
            $table->foreignId('order_id')->constrained('order_items')->onDelete('cascade');

            // Information about the delivery itself
            $table->timestamp('date')->nullable(); // Scheduled or actual delivery date/time
            $table->enum('status', DeliveryStatusEnum::values())->default(DeliveryStatusEnum::OrderReceived->value);
            $table->string('full_name');
            $table->string('phone_number')->nullable();
            $table->text('address_line_1'); // Specific local delivery address (could be different from billing)
            $table->text('address_line_2')->nullable(); // Specific local delivery address (could be different from billing)
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('instructions')->nullable(); // Special instructions for the delivery person

            // Information about the delivery person/method (optional, but useful)
            $table->unsignedBigInteger('courier_id')->nullable(); // Foreign key to a 'users' table
            $table->foreign('courier_id')->references('id')->on('users')->onDelete('set null'); // Assuming couriers are in the 'users' table
            $table->string('method')->nullable(); // E.g., "In-house driver", "Local Courier A", "Bicycle Courier"

            // Tracking information (if applicable)
            $table->string('tracking_number')->nullable(); // If using a local courier with tracking

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
