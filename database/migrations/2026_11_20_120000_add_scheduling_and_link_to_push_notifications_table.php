<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_notifications', function (Blueprint $table): void {
            $table->string('link_url', 2048)->nullable()->after('image_path');
            $table->string('action_text', 60)->nullable()->after('link_url');
            $table->timestamp('scheduled_at')->nullable()->after('notification_date');
            $table->timestamp('sent_at')->nullable()->after('scheduled_at');
            $table->timestamp('cancelled_at')->nullable()->after('sent_at');
            $table->index(['status', 'scheduled_at'], 'push_notifications_status_scheduled_idx');
        });
    }

    public function down(): void
    {
        Schema::table('push_notifications', function (Blueprint $table): void {
            $table->dropIndex('push_notifications_status_scheduled_idx');
            $table->dropColumn([
                'link_url',
                'action_text',
                'scheduled_at',
                'sent_at',
                'cancelled_at',
            ]);
        });
    }
};
