<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'team_banner_mode')) {
                $table->string('team_banner_mode', 20)->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('users', 'team_banner_color')) {
                $table->string('team_banner_color', 20)->nullable()->after('team_banner_mode');
            }
            if (! Schema::hasColumn('users', 'team_banner_media_url')) {
                $table->string('team_banner_media_url')->nullable()->after('team_banner_color');
            }
            if (! Schema::hasColumn('users', 'team_banner_media_path')) {
                $table->string('team_banner_media_path')->nullable()->after('team_banner_media_url');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $columns = [
                'team_banner_mode',
                'team_banner_color',
                'team_banner_media_url',
                'team_banner_media_path',
            ];
            $existing = array_values(array_filter($columns, static fn (string $column): bool => Schema::hasColumn('users', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
