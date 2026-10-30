<?php

namespace Database\Seeders;

use App\Models\Legajo;
use Illuminate\Database\Seeder;

class LegajoExtraSeeder extends Seeder
{
    public function run(): void
    {
        $legajos = [
            [1011, 'Álvarez', 'Matías Ezequiel', '39112233', '20391122334', '1996-04-11', 1, 'Av. España', '410', '5870', 'Villa Dolores', 'X', '+54 9 3544 51-1101', '2021-08-16', null],
            [1012, 'Torres', 'Camila Rocío', '40223344', '27402233449', '1997-10-22', 2, '25 de Mayo', '89', '5000', 'Córdoba', 'X', '+54 9 351 620-2202', '2022-01-10', null],
            [1013, 'Ruiz', 'Gonzalo Nicolás', '30556677', '20305566778', '1984-07-03', 1, 'Chacabuco', '730', '5885', 'Mina Clavero', 'X', '+54 9 3544 52-3303', '2015-11-02', null],
            [1014, 'Ramírez', 'Sofía Macarena', '41334455', '27413344556', '1998-02-19', 2, 'Alvear', '315', '5870', 'Villa Dolores', 'X', '+54 9 3544 53-4404', '2022-06-01', null],
            [1015, 'Flores', 'Federico Agustín', '33778899', '20337788991', '1988-12-08', 1, '9 de Julio', '1180', '5000', 'Córdoba', 'X', '+54 9 351 630-5505', '2018-03-12', null],
            [1016, 'Benítez', 'Micaela Victoria', '36990011', '27369900112', '1993-05-25', 2, 'Dean Funes', '64', '5891', 'Villa Cura Brochero', 'X', '+54 9 3544 54-6606', '2019-09-09', null],
            [1017, 'Acosta', 'Leandro Gabriel', '29445566', '20294455665', '1982-09-14', 1, 'Pueyrredón', '522', '5870', 'Villa Dolores', 'X', '+54 9 3544 55-7707', '2013-04-22', null],
            [1018, 'Medina', 'Julieta Antonella', '42667788', '27426677883', '1999-11-30', 2, 'Vélez Sársfield', '950', '5000', 'Córdoba', 'X', '+54 9 351 640-8808', '2023-02-06', null],
            [1019, 'Herrera', 'Facundo Emanuel', '35889900', '20358899004', '1991-01-17', 1, 'Lavalle', '205', '5885', 'Mina Clavero', 'X', '+54 9 3544 56-9909', '2017-07-18', null],
            [1020, 'Castro', 'Carolina Beatriz', '31667788', '27316677880', '1985-06-28', 2, 'Maipú', '380', '5870', 'Villa Dolores', 'X', '+54 9 3544 57-0010', '2016-10-03', '2026-03-31'],
        ];

        $cargoSeccion = [
            1011 => ['CAJ', 'VEN'],
            1012 => ['CAJ', 'VEN'],
            1013 => ['REP', 'DEP'],
            1014 => ['ADM', 'ADM'],
            1015 => ['CAR', 'CAR'],
            1016 => ['CAJ', 'VEN'],
            1017 => ['SUP', 'VEN'],
            1018 => ['REP', 'DEP'],
            1019 => ['MAN', 'MAN'],
            1020 => ['ADM', 'ADM'],
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

