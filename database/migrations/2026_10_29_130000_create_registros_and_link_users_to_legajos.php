<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('leg_numero')->nullable()->unique();
            $table->string('dni', 9)->nullable()->index();
            $table->string('celular', 30)->nullable();
        });

        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('leg_numero')->index();
            $table->string('dni', 9);
            $table->string('email')->nullable();
            $table->string('genero', 10)->nullable();
            $table->string('domicilio')->nullable();
            $table->string('localidad', 60)->nullable();
            $table->string('provincia', 60)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->unsignedTinyInteger('paso_actual')->default(2);
            $table->string('estado', 20)->default('en_curso')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['leg_numero']);
            $table->dropIndex(['dni']);
            $table->dropColumn(['leg_numero', 'dni', 'celular']);
        });
    }
};

