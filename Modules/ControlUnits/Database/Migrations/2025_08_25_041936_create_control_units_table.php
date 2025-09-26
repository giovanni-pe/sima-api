<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('control_units', function (Blueprint $t) {
            $t->id();
            $t->string('serial_code', 255);
            $t->string('model', 255);
            $t->string('installed_at', 255)->nullable();
            $t->string('status', 255);
            $t->foreignId('parcel_id')->constrained('parcels')->onDelete('cascade');
            $t->string('mqtt_client_id', 255);
            $t->string('mqtt_username', 255)->nullable();
            $t->string('mqtt_password_enc', 255)->nullable();
            $t->string('status_topic', 255)->nullable();
            $t->string('lwt_topic', 255)->nullable();
            $t->string('last_seen_at', 255)->nullable();
            $t->boolean('active');
            $t->timestamps();
            $t->softDeletes();
            $t->index('parcel_id');
            $t->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_units');
    }
};