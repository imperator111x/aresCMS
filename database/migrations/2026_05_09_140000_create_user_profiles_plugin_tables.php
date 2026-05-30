<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('addressee_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('pending'); // pending | accepted | declined
            $table->timestamps();

            $table->unique(['requester_id', 'addressee_id']);
            $table->index(['addressee_id', 'status']);
            $table->index(['requester_id', 'status']);
        });

        Schema::create('profile_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('friendship_id')->constrained('profile_friendships')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->longText('body');
            $table->boolean('is_e2e')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['friendship_id', 'id']);
        });

        Schema::create('profile_e2e_public_keys', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->text('public_key_jwk');
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_chat_messages');
        Schema::dropIfExists('profile_e2e_public_keys');
        Schema::dropIfExists('profile_friendships');
    }
};
