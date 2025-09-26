<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_code', 10)->unique(); // Código PIN
            $table->foreignId('passenger_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_type_id')->constrained()->onDelete('cascade');

            // Ubicaciones
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->string('pickup_address');
            $table->decimal('destination_latitude', 10, 8);
            $table->decimal('destination_longitude', 11, 8);
            $table->string('destination_address');

            // Estado del viaje
            $table->enum('status', [
                'pending',           // Buscando conductor
                'driver_assigned',   // Conductor asignado
                'driver_en_route',   // Conductor en camino
                'driver_arrived',    // Conductor llegó
                'in_progress',       // Viaje en curso
                'completed',         // Completado
                'cancelled_by_passenger',
                'cancelled_by_driver',
                'cancelled_by_system',
                'no_drivers_available'
            ])->default('pending');

            // Precios y tarifas
            $table->decimal('passenger_max_fare', 8, 2)->nullable();
            $table->decimal('estimated_fare', 8, 2);
            $table->decimal('final_fare', 8, 2)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('waiting_time_minutes', 8, 2)->default(0);
            $table->decimal('cancellation_fee', 8, 2)->default(0);

            // Notas y observaciones
            $table->text('passenger_notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Timestamps del proceso
            $table->timestamp('requested_at');
            $table->timestamp('driver_assigned_at')->nullable();
            $table->timestamp('driver_arrived_at')->nullable();
            $table->timestamp('trip_started_at')->nullable();
            $table->timestamp('trip_completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Configuración de timeouts y límites
            $table->integer('search_attempts')->default(0);
            $table->decimal('search_radius_km', 8, 2)->default(5);
            $table->integer('max_search_radius_km')->default(20);

            $table->timestamps();

            $table->index(['status', 'requested_at']);
            $table->index(['passenger_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('trip_code');
            $table->boolean('is_collective')->default(false); // ¿Es colectivo?
            $table->tinyInteger('max_passengers')->nullable(); // Capacidad máxima (opcional si lo quieres sobrescribir)

        });
    }

    public function down()
    {
        Schema::dropIfExists('trips');
    }
};
