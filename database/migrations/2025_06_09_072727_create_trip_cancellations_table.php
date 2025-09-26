<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('cancelled_by')->constrained('users')->onDelete('cascade');
            $table->enum('cancelled_by_type', ['passenger', 'driver', 'system']);
            $table->enum('cancellation_reason', [
                'passenger_no_show',
                'driver_no_show',
                'driver_inactive',
                'passenger_request',
                'driver_request',
                'system_timeout',
                'no_drivers_available',
                'emergency'
            ]);
            $table->text('custom_reason')->nullable();
            $table->decimal('penalty_fee', 8, 2)->default(0);
            $table->boolean('fee_applied')->default(false);
            $table->timestamps();

            $table->index(['trip_id']);
            $table->index(['cancelled_by', 'cancelled_by_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_cancellations');
    }
};
