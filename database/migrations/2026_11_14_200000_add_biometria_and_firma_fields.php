<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros', function (Blueprint $table): void {
            $table->string('foto_biometrica_path')->nullable()->after('dni_dorso_path');
            $table->boolean('biometria_validada')->default(false)->after('foto_biometrica_path');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('clave_firma')->nullable()->after('password');
            $table->string('rubrica_path')->nullable()->after('clave_firma');
        });
    }

    public function down(): void
    {
        Schema::table('registros', function (Blueprint $table): void {
            $table->dropColumn(['foto_biometrica_path', 'biometria_validada']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['clave_firma', 'rubrica_path']);
        });
    }
};
