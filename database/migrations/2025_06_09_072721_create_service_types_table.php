<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Económico, Premium, Compartido
            $table->string('description')->nullable();
            $table->decimal('base_rate', 8, 2);
            $table->decimal('per_km_rate', 8, 2);
            $table->decimal('per_minute_rate', 8, 2);
            $table->decimal('minimum_fare', 8, 2);
            $table->decimal('cancellation_fee', 8, 2);
            $table->decimal('waiting_fee_per_minute', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_types');
    }
};
