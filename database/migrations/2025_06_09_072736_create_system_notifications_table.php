<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('notification_type', [
                'trip_request',
                'driver_assignment',
                'driver_arrival',
                'trip_start',
                'trip_completion',
                'cancellation',
                'rating_request',
                'system_message'
            ]);
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Datos adicionales para la notificación
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['notification_type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_notifications');
    }
};
