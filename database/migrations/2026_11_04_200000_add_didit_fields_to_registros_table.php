<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->string('dni_frente_path')->nullable()->after('fecha_nacimiento');
            $table->string('dni_dorso_path')->nullable()->after('dni_frente_path');
            $table->string('didit_request_id', 64)->nullable()->after('dni_dorso_path');
            $table->string('didit_status', 30)->nullable()->after('didit_request_id');
            $table->json('didit_payload')->nullable()->after('didit_status');
        });
    }

    public function down(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->dropColumn([
                'dni_frente_path',
                'dni_dorso_path',
                'didit_request_id',
                'didit_status',
                'didit_payload',
            ]);
        });
    }
};

