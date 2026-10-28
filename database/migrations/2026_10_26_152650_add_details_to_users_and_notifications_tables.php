<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('legajo')->nullable()->after('email')->index();
            $table->string('cargo_sap')->nullable()->after('legajo')->index();
            $table->string('seccion')->nullable()->after('cargo_sap')->index();
        });

        Schema::table('push_notifications', function (Blueprint $table) {
            $table->string('sender')->nullable()->after('id');
            $table->boolean('sender_show_section')->default(false)->after('sender');
            $table->string('reason')->nullable()->after('sender_show_section')->index();
            $table->string('priority')->default('normal')->after('reason');
            $table->date('notification_date')->nullable()->after('priority');
            $table->string('image_path')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['legajo', 'cargo_sap', 'seccion']);
        });

        Schema::table('push_notifications', function (Blueprint $table) {
            $table->dropColumn(['sender', 'sender_show_section', 'reason', 'priority', 'notification_date', 'image_path']);
        });
    }
};
