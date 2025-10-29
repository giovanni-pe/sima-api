<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $t) {
            $t->id();
            $t->string('name', 255);
            $t->foreignId('control_unit_id')->constrained('control_units')->onDelete('cascade')
            ->onUpdate('cascade');
            $t->string('type', 255);
            $t->boolean('active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};