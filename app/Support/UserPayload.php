<?php

namespace App\Support;

use App\Models\Legajo;
use App\Models\Registro;
use App\Models\User;

class UserPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function format(User $user): array
    {
        $legajo = $user->leg_numero ? Legajo::find($user->leg_numero) : null;
        $registro = $user->id ? Registro::where('user_id', $user->id)->latest()->first() : null;

        $domicilioPartes = array_filter([
            $registro?->domicilio ?? $legajo?->leg_domic,
            $registro?->localidad ?? $legajo?->leg_locali,
            $registro?->provincia,
        ]);
        $domicilio = ! empty($domicilioPartes) ? implode(', ', $domicilioPartes) : '';

        $tieneFotoPerfil = ! empty($user->foto_perfil_path);
        $fotoVersion = $tieneFotoPerfil
            ? ($user->foto_perfil_updated_at?->timestamp ?? $user->updated_at?->timestamp ?? 1)
            : null;
        $fotoPerfilUrl = $tieneFotoPerfil ? "/user/foto-perfil?v={$fotoVersion}" : null;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'legajo' => $user->leg_numero ? (string) $user->leg_numero : ($user->legajo ?? ''),
            'dni' => $user->dni ?? $legajo?->leg_numdoc ?? '',
            'cuil' => $legajo?->leg_cuil ?? '',
            'celular' => $user->celular ?? $registro?->telefono ?? $legajo?->leg_telefono ?? '',
            'cargo' => $user->cargo_sap ?? '',
            'seccion' => $user->seccion ?? '',
            'domicilio' => $domicilio,
            'fecha_ingreso' => $legajo?->leg_fecing?->format('d/m/Y') ?? '',
            'tiene_rubrica' => ! empty($user->rubrica_path),
            'tiene_clave_firma' => ! empty($user->clave_firma),
            'tiene_foto_perfil' => $tieneFotoPerfil,
            'foto_perfil_url' => $fotoPerfilUrl,
        ];
    }
}
