<?php

namespace Database\Seeders;

use App\Models\Legajo;
use App\Models\User;
use App\Support\CatalogoBejerman;
use Illuminate\Database\Seeder;

class SincronizarCargoSeccionUsuariosSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->whereNotNull('leg_numero')
            ->each(function (User $user): void {
                $legajo = Legajo::query()->find($user->leg_numero);

                if ($legajo === null) {
                    return;
                }

                $user->update([
                    'cargo_sap' => CatalogoBejerman::cargo($legajo->car_codigo),
                    'seccion' => CatalogoBejerman::seccion($legajo->sec_codigo),
                ]);
            });
    }
}

