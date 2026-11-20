<?php

namespace Database\Seeders;

use App\Models\Legajo;
use Illuminate\Database\Seeder;

class LegajoPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $legajos = [
            [3001, 'Gómez', 'Lucas Martín', '43003001', '20430030011', '1994-03-12', 1, 'Av. San Martín', '110', '5870', 'Villa Dolores', 'X', '+54 9 3544 61-3001', '2023-01-15', null],
            [3002, 'Pereyra', 'Valentina Sol', '43003002', '27430030025', '1997-07-24', 2, 'Belgrano', '220', '5870', 'Villa Dolores', 'X', '+54 9 3544 61-3002', '2023-02-01', null],
            [3003, 'Sosa', 'Diego Armando', '43003003', '20430030038', '1987-11-05', 1, 'Rivadavia', '330', '5000', 'Córdoba', 'X', '+54 9 351 610-3003', '2022-05-10', null],
            [3004, 'Molina', 'Agustina Belén', '43003004', '27430030042', '1998-01-19', 2, 'Sarmiento', '440', '5000', 'Córdoba', 'X', '+54 9 351 610-3004', '2023-03-20', null],
            [3005, 'Romero', 'Cristian Javier', '43003005', '20430030054', '1985-06-30', 1, 'Mitre', '550', '5885', 'Mina Clavero', 'X', '+54 9 3544 61-3005', '2021-08-02', null],
            [3006, 'Suárez', 'Nicolás Ezequiel', '43003006', '20430030067', '1992-09-14', 1, 'Colón', '660', '5870', 'Villa Dolores', 'X', '+54 9 3544 61-3006', '2022-11-14', null],
            [3007, 'Díaz', 'Florencia Abigail', '43003007', '27430030071', '1995-12-03', 2, 'Independencia', '770', '5000', 'Córdoba', 'X', '+54 9 351 610-3007', '2023-04-03', null],
            [3008, 'Vega', 'Ramiro Andrés', '43003008', '20430030089', '1989-04-27', 1, 'Libertad', '880', '5000', 'Córdoba', 'X', '+54 9 351 610-3008', '2022-09-01', null],
            [3009, 'Rojas', 'Emanuel David', '43003009', '20430030096', '1986-08-18', 1, 'Urquiza', '990', '5891', 'Villa Cura Brochero', 'X', '+54 9 3544 61-3009', '2020-07-06', null],
            [3010, 'Peralta', 'Jonathan Isaías', '43003010', '20430030102', '1993-02-09', 1, 'Moreno', '1010', '5870', 'Villa Dolores', 'X', '+54 9 3544 61-3010', '2023-06-12', null],
            [3011, 'Navarro', 'Mariana Celeste', '43003011', '27430030115', '1983-10-21', 2, '25 de Mayo', '1120', '5870', 'Villa Dolores', 'X', '+54 9 3544 61-3011', '2019-10-01', null],
            [3012, 'Quiroga', 'Tomás Ignacio', '43003012', '20430030128', '2000-05-07', 1, '9 de Julio', '1230', '5000', 'Córdoba', 'X', '+54 9 351 610-3012', '2024-01-08', null],
        ];

        $cargoSeccion = [
            3001 => ['CAJ', 'VEN'],
            3002 => ['CAJ', 'VEN'],
            3003 => ['REP', 'DEP'],
            3004 => ['REP', 'DEP'],
            3005 => ['CAR', 'CAR'],
            3006 => ['CAR', 'CAR'],
            3007 => ['ADM', 'ADM'],
            3008 => ['ADM', 'ADM'],
            3009 => ['MAN', 'MAN'],
            3010 => ['MAN', 'MAN'],
            3011 => ['SUP', 'VEN'],
            3012 => ['CAJ', 'VEN'],
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
