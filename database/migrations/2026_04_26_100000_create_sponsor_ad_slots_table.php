<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sponsor_ad_slots')) {
            return;
        }

        Schema::create('sponsor_ad_slots', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slot_key', 100)->index();
            $table->string('target_url')->nullable();
            $table->string('image_url')->nullable();
            $table->text('html_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_ad_slots');
    }
};
