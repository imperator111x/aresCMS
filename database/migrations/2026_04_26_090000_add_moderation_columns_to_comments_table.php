<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            if (! Schema::hasColumn('comments', 'moderation_status')) {
                $table->string('moderation_status', 20)->default('approved')->after('content');
            }
            if (! Schema::hasColumn('comments', 'moderation_score')) {
                $table->unsignedTinyInteger('moderation_score')->nullable()->after('moderation_status');
            }
            if (! Schema::hasColumn('comments', 'moderation_flags')) {
                $table->json('moderation_flags')->nullable()->after('moderation_score');
            }
            if (! Schema::hasColumn('comments', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('moderation_flags');
            }
            if (! Schema::hasColumn('comments', 'moderated_by_user_id')) {
                $table->foreignId('moderated_by_user_id')->nullable()->after('moderated_at')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            if (Schema::hasColumn('comments', 'moderated_by_user_id')) {
                $table->dropForeign(['moderated_by_user_id']);
                $table->dropColumn('moderated_by_user_id');
            }
            foreach (['moderated_at', 'moderation_flags', 'moderation_score', 'moderation_status'] as $column) {
                if (Schema::hasColumn('comments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
