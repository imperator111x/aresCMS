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
            if (! Schema::hasColumn('pages', 'show_hero')) {
                $table->boolean('show_hero')->default(false)->after('is_published');
            }
            if (! Schema::hasColumn('pages', 'hero_badge')) {
                $table->string('hero_badge', 120)->nullable()->after('show_hero');
            }
            if (! Schema::hasColumn('pages', 'hero_heading')) {
                $table->string('hero_heading', 255)->nullable()->after('hero_badge');
            }
            if (! Schema::hasColumn('pages', 'hero_subheading')) {
                $table->string('hero_subheading', 500)->nullable()->after('hero_heading');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table): void {
            foreach (['hero_subheading', 'hero_heading', 'hero_badge', 'show_hero'] as $column) {
                if (Schema::hasColumn('pages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

