<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('admin_notif_last_seen_at')->nullable()->after('two_factor_confirmed_at');
            $table->timestamp('admin_notif_feed_floor_at')->nullable()->after('admin_notif_last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_notif_last_seen_at', 'admin_notif_feed_floor_at']);
        });
    }
};
