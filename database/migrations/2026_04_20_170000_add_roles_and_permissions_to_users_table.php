<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->nullable()->after('is_admin');
            $table->json('admin_permissions')->nullable()->after('role');
        });

        DB::table('users')
            ->where('is_admin', true)
            ->update([
                'role' => 'admin',
                'admin_permissions' => json_encode(['*']),
            ]);

        $owner = DB::table('users')
            ->where('is_admin', true)
            ->orderBy('id')
            ->first();

        if ($owner) {
            DB::table('users')
                ->where('id', $owner->id)
                ->update([
                    'role' => 'owner',
                    'admin_permissions' => json_encode(['*']),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'admin_permissions']);
        });
    }
};

