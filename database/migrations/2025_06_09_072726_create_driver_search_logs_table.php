<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('driver_search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['sent', 'accepted', 'rejected', 'timeout'])->default('sent');
            $table->timestamp('sent_at');
            $table->timestamp('responded_at')->nullable();
            $table->decimal('driver_distance_km', 8, 2);
            $table->timestamps();

            $table->index(['trip_id', 'status']);
            $table->index(['driver_id', 'sent_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_search_logs');
    }
};
