<?php

namespace Database\Seeders;

use App\Models\Legajo;
use App\Models\User;
use App\Support\CatalogoBejerman;
use Illuminate\Database\Seeder;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [2001, 'Gómez', 'Lucas Martín', '38101001', '20381010011', '1994-03-12', 1, 'CAJ', 'VEN', 'lucas.gomez@prueba.cyre.com.ar', '+54 9 3544 61-0001'],
            [2002, 'Pereyra', 'Valentina Sol', '40101002', '27401010025', '1997-07-24', 2, 'CAJ', 'VEN', 'valentina.pereyra@prueba.cyre.com.ar', '+54 9 3544 61-0002'],
            [2003, 'Sosa', 'Diego Armando', '33101003', '20331010038', '1987-11-05', 1, 'REP', 'DEP', 'diego.sosa@prueba.cyre.com.ar', '+54 9 351 610-0003'],
            [2004, 'Molina', 'Agustina Belén', '41101004', '27411010042', '1998-01-19', 2, 'REP', 'DEP', 'agustina.molina@prueba.cyre.com.ar', '+54 9 351 610-0004'],
            [2005, 'Romero', 'Cristian Javier', '31101005', '20311010054', '1985-06-30', 1, 'CAR', 'CAR', 'cristian.romero@prueba.cyre.com.ar', '+54 9 3544 61-0005'],
            [2006, 'Suárez', 'Nicolás Ezequiel', '36101006', '20361010067', '1992-09-14', 1, 'CAR', 'CAR', 'nicolas.suarez@prueba.cyre.com.ar', '+54 9 3544 61-0006'],
            [2007, 'Díaz', 'Florencia Abigail', '39101007', '27391010071', '1995-12-03', 2, 'ADM', 'ADM', 'florencia.diaz@prueba.cyre.com.ar', '+54 9 351 610-0007'],
            [2008, 'Vega', 'Ramiro Andrés', '34101008', '20341010089', '1989-04-27', 1, 'ADM', 'ADM', 'ramiro.vega@prueba.cyre.com.ar', '+54 9 351 610-0008'],
            [2009, 'Rojas', 'Emanuel David', '32101009', '20321010096', '1986-08-18', 1, 'MAN', 'MAN', 'emanuel.rojas@prueba.cyre.com.ar', '+54 9 3544 61-0009'],
            [2010, 'Peralta', 'Jonathan Isaías', '37101010', '20371010102', '1993-02-09', 1, 'MAN', 'MAN', 'jonathan.peralta@prueba.cyre.com.ar', '+54 9 3544 61-0010'],
            [2011, 'Navarro', 'Mariana Celeste', '30101011', '27301010115', '1983-10-21', 2, 'SUP', 'VEN', 'mariana.navarro@prueba.cyre.com.ar', '+54 9 3544 61-0011'],
            [2012, 'Quiroga', 'Tomás Ignacio', '42101012', '20421010128', '2000-05-07', 1, 'CAJ', 'VEN', 'tomas.quiroga@prueba.cyre.com.ar', '+54 9 351 610-0012'],
        ];

        foreach ($usuarios as [$legNumero, $apellido, $nombre, $dni, $cuil, $fecnac, $sexo, $carCodigo, $secCodigo, $email, $celular]) {
            Legajo::updateOrCreate(
                ['leg_numero' => $legNumero],
                [
                    'car_codigo' => $carCodigo,
                    'sec_codigo' => $secCodigo,
                    'leg_apellido' => $apellido,
                    'leg_nombre' => $nombre,
                    'leg_tipodoc' => 1,
                    'leg_numdoc' => $dni,
                    'leg_cuil' => $cuil,
                    'leg_fecnac' => $fecnac,
                    'leg_sexo' => $sexo,
                    'leg_domic' => 'Av. San Martín',
                    'leg_puerta' => (string) (100 + ($legNumero - 2000) * 10),
                    'leg_codpos' => '5870',
                    'leg_locali' => 'Villa Dolores',
                    'leg_prvdom' => 'X',
                    'leg_prvtra' => 'X',
                    'Leg_celular' => $celular,
                    'Leg_mail' => $email,
                    'leg_fecing' => '2023-01-15',
                    'leg_fecegr' => null,
                ]
            );

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $apellido . ', ' . $nombre,
                    'password' => '12345678',
                    'clave_firma' => '1234',
                    'role' => 'empleado',
                    'legajo' => (string) $legNumero,
                    'leg_numero' => $legNumero,
                    'dni' => $dni,
                    'celular' => $celular,
                    'cargo_sap' => CatalogoBejerman::cargo($carCodigo),
                    'seccion' => CatalogoBejerman::seccion($secCodigo),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
