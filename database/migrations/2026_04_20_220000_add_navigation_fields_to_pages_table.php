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
            if (! Schema::hasColumn('pages', 'show_in_navigation')) {
                $table->boolean('show_in_navigation')->default(false)->after('is_published');
            }
            if (! Schema::hasColumn('pages', 'navigation_label')) {
                $table->string('navigation_label', 80)->nullable()->after('show_in_navigation');
            }
            if (! Schema::hasColumn('pages', 'navigation_order')) {
                $table->unsignedInteger('navigation_order')->default(0)->after('navigation_label');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table): void {
            $dropColumns = [];
            foreach (['show_in_navigation', 'navigation_label', 'navigation_order'] as $column) {
                if (Schema::hasColumn('pages', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};

