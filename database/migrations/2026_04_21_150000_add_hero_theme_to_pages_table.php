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
            if (! Schema::hasColumn('pages', 'hero_theme')) {
                $table->string('hero_theme', 40)->default('blue')->after('hero_subheading');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table): void {
            if (Schema::hasColumn('pages', 'hero_theme')) {
                $table->dropColumn('hero_theme');
            }
        });
    }
};

