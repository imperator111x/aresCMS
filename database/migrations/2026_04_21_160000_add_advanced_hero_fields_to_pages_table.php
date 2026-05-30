<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('pages', 'hero_background_image')) {
                $table->string('hero_background_image')->nullable()->after('hero_theme');
            }
            if (! Schema::hasColumn('pages', 'hero_overlay_strength')) {
                $table->string('hero_overlay_strength', 20)->default('medium')->after('hero_background_image');
            }
            if (! Schema::hasColumn('pages', 'hero_height')) {
                $table->string('hero_height', 20)->default('md')->after('hero_overlay_strength');
            }
            if (! Schema::hasColumn('pages', 'hero_primary_button_text')) {
                $table->string('hero_primary_button_text', 120)->nullable()->after('hero_height');
            }
            if (! Schema::hasColumn('pages', 'hero_primary_button_url')) {
                $table->string('hero_primary_button_url')->nullable()->after('hero_primary_button_text');
            }
            if (! Schema::hasColumn('pages', 'hero_secondary_button_text')) {
                $table->string('hero_secondary_button_text', 120)->nullable()->after('hero_primary_button_url');
            }
            if (! Schema::hasColumn('pages', 'hero_secondary_button_url')) {
                $table->string('hero_secondary_button_url')->nullable()->after('hero_secondary_button_text');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table): void {
            $columns = [
                'hero_background_image',
                'hero_overlay_strength',
                'hero_height',
                'hero_primary_button_text',
                'hero_primary_button_url',
                'hero_secondary_button_text',
                'hero_secondary_button_url',
            ];
            $existing = array_values(array_filter($columns, static fn (string $column): bool => Schema::hasColumn('pages', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};

