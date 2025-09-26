<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $t) {
            $t->id();

            // Datos principales
            $t->string('name', 120);
            $t->string('location', 255)->nullable();
            $t->decimal('area_m2', 10, 2);

            // Relación opcional con usuarios (quien supervisa/creó)
            $t->foreignId('user_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Geolocalización (7 decimales ~ 1.1cm)
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();

            // Cultivo y estado
            $t->string('crop_type', 100)->nullable();
            $t->boolean('active')->default(true);

            // Timestamps y soft deletes
            $t->timestamps();
            $t->softDeletes();

            // Índices útiles
            $t->index(['user_id', 'created_at']);
            $t->index(['latitude', 'longitude']);
            $t->index('active');
            $t->index('crop_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
