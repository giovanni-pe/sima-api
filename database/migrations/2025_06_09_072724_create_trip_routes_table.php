<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('recorded_at');
            $table->enum('route_type', ['to_pickup', 'in_trip']); // Hacia pickup o durante el viaje
            $table->timestamps();

            $table->index(['trip_id', 'route_type', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_routes');
    }
};
