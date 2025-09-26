<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->string('pickup_address');
            $table->decimal('destination_latitude', 10, 8);
            $table->decimal('destination_longitude', 11, 8);
            $table->string('destination_address');

            $table->text('passenger_notes')->nullable();         // Mensaje al conductor
            $table->timestamp('pickup_eta')->nullable();         // ETA actualizado
            $table->timestamp('boarded_at')->nullable();         // Hora en que abordó
            $table->timestamp('dropped_at')->nullable();         // Hora en que bajó

            $table->enum('status', ['reserved', 'aboard', 'dropped', 'cancelled'])->default('reserved');

            $table->decimal('fare', 8, 2)->nullable();           // Tarifa individual
            $table->tinyInteger('seat_number')->nullable();      // Número de asiento si quieres

            $table->string('whatsapp_contact', 20)->nullable();  // <--- WhatsApp opcional

            $table->timestamps();

            $table->unique(['trip_id', 'user_id']); // Un pasajero solo una vez por viaje

            $table->index(['trip_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_passengers');
    }
};

