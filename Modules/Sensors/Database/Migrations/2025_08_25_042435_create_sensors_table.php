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
            $t->string('type', 255);
            $t->boolean('active');
            $t->timestamps();
            $t->softDeletes();
            $t->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};