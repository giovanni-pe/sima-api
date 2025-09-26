<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->decimal('base_fare', 8, 2);
            $table->decimal('distance_fare', 8, 2);
            $table->decimal('time_fare', 8, 2);
            $table->decimal('waiting_fare', 8, 2)->default(0);
            $table->decimal('traffic_adjustment', 8, 2)->default(0);
            $table->decimal('total_amount', 8, 2);
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'digital_wallet']);
            $table->string('transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['trip_id']);
            $table->index(['payment_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_payments');
    }
};
