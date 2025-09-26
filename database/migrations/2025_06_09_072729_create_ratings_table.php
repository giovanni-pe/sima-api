<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('rated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('rated_user')->constrained('users')->onDelete('cascade');
            $table->enum('rating_type', ['passenger_to_driver', 'driver_to_passenger']);
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['trip_id', 'rating_type']);
            $table->index(['rated_user', 'rating']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
