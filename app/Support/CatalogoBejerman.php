<?php

namespace App\Support;

class CatalogoBejerman
{
    private const CARGOS = [
        'CAJ' => 'Cajero',
        'REP' => 'Repositor',
        'CAR' => 'Carnicero',
        'ADM' => 'Administrativo',
        'MAN' => 'Mantenimiento',
        'SUP' => 'Supervisor',
    ];

    private const SECCIONES = [
        'VEN' => 'Ventas',
        'DEP' => 'Depósito',
        'CAR' => 'Carnicería',
        'ADM' => 'Administración',
        'MAN' => 'Mantenimiento',
    ];

    public static function cargo(?string $codigo): ?string
    {
        return self::traducir(self::CARGOS, $codigo);
    }

    public static function seccion(?string $codigo): ?string
    {
        return self::traducir(self::SECCIONES, $codigo);
    }

    /**
     * @param  array<string, string>  $catalogo
     */
    private static function traducir(array $catalogo, ?string $codigo): ?string
    {
        $clave = strtoupper(trim((string) $codigo));

        if ($clave === '') {
            return null;
        }

        return $catalogo[$clave] ?? $clave;
    }
}

