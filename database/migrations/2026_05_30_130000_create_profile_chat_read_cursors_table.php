<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_chat_read_cursors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('friendship_id')->constrained('profile_friendships')->cascadeOnDelete();
            $table->unsignedBigInteger('last_read_message_id')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'friendship_id']);
            $table->index(['user_id', 'last_read_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_chat_read_cursors');
    }
};
