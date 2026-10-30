<?php

namespace Database\Seeders;

use App\Models\Legajo;
use Illuminate\Database\Seeder;

class LegajoSeeder extends Seeder
{
    public function run(): void
    {
        $legajos = [
            [1001, 'Pérez', 'Juan Martín', '30123456', '20301234568', '1983-05-14', 1, 'Belgrano', '245', '5870', 'Villa Dolores', 'X', '+54 9 3544 55-1234', '2015-03-01', null],
            [1002, 'Gómez', 'María Laura', '32456789', '27324567894', '1986-11-02', 2, 'San Martín', '1020', '5000', 'Córdoba', 'X', '+54 9 351 612-3344', '2016-07-15', null],
            [1003, 'Rodríguez', 'Carlos Alberto', '28987654', '20289876543', '1981-02-20', 1, 'Rivadavia', '88', '5870', 'Villa Dolores', 'X', '+54 9 3544 41-2233', '2012-01-10', null],
            [1004, 'Fernández', 'Lucía', '35111222', '27351112229', '1990-08-30', 2, 'Sarmiento', '512', '5885', 'Mina Clavero', 'X', '+54 9 3544 47-8899', '2018-04-02', null],
            [1005, 'López', 'Diego Hernán', '33444555', '20334445557', '1988-01-12', 1, 'Mitre', '77', '5000', 'Córdoba', 'X', '+54 9 351 555-6677', '2017-09-20', null],
            [1006, 'Martínez', 'Ana Belén', '37888999', '27378889996', '1994-06-05', 2, 'Colón', '1450', '5870', 'Villa Dolores', 'X', '+54 9 3544 50-1122', '2020-02-17', null],
            [1007, 'García', 'Pablo Andrés', '31222333', '20312223331', '1985-12-24', 1, 'Independencia', '300', '5891', 'Villa Cura Brochero', 'X', '+54 9 3544 46-5544', '2014-06-01', null],
            [1008, 'Sánchez', 'Valeria Soledad', '36555666', '27365556664', '1992-03-18', 2, 'Libertad', '9', '5000', 'Córdoba', 'X', '+54 9 351 777-8899', '2019-11-04', null],
            [1009, 'Romero', 'Sebastián', '34777888', '20347778882', '1989-09-09', 1, 'Urquiza', '640', '5870', 'Villa Dolores', 'X', '+54 9 3544 43-6677', '2016-01-25', null],
            [1010, 'Díaz', 'Florencia', '38999000', '27389990007', '1995-07-27', 2, 'Moreno', '150', '5885', 'Mina Clavero', 'X', '+54 9 3544 48-0011', '2021-05-03', '2025-12-31'],
        ];

        $cargoSeccion = [
            1001 => ['CAJ', 'VEN'],
            1002 => ['CAJ', 'VEN'],
            1003 => ['REP', 'DEP'],
            1004 => ['CAR', 'CAR'],
            1005 => ['REP', 'DEP'],
            1006 => ['CAJ', 'VEN'],
            1007 => ['ADM', 'ADM'],
            1008 => ['CAR', 'CAR'],
            1009 => ['MAN', 'MAN'],
            1010 => ['SUP', 'VEN'],
        ];

        foreach ($legajos as [$numero, $apellido, $nombre, $dni, $cuil, $fecnac, $sexo, $domic, $puerta, $codpos, $locali, $prov, $celular, $fecing, $fecegr]) {
            Legajo::updateOrCreate(
                ['leg_numero' => $numero],
                [
                    'car_codigo' => $cargoSeccion[$numero][0],
                    'sec_codigo' => $cargoSeccion[$numero][1],
                    'leg_apellido' => $apellido,
                    'leg_nombre' => $nombre,
                    'leg_tipodoc' => 1,
                    'leg_numdoc' => $dni,
                    'leg_cuil' => $cuil,
                    'leg_fecnac' => $fecnac,
                    'leg_sexo' => $sexo,
                    'leg_domic' => $domic,
                    'leg_puerta' => $puerta,
                    'leg_codpos' => $codpos,
                    'leg_locali' => $locali,
                    'leg_prvdom' => $prov,
                    'leg_prvtra' => $prov,
                    'Leg_celular' => $celular,
                    'Leg_mail' => null,
                    'leg_fecing' => $fecing,
                    'leg_fecegr' => $fecegr,
                ]
            );
        }
    }
}

