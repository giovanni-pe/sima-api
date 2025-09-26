<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->string('chat_uuid')->unique(); // UUID único para el chat
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at'); // Chat temporal que expira
            $table->timestamps();

            $table->index(['trip_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_chats');
    }
};
