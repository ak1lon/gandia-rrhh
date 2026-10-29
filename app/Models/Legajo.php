<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legajo extends Model
{
    protected $table = 'legajos';

    protected $primaryKey = 'leg_numero';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'leg_numero' => 'integer',
            'leg_fecnac' => 'date',
            'leg_fecing' => 'date',
            'leg_fecegr' => 'date',
            'leg_fecingr' => 'date',
            'leg_discap' => 'boolean',
            'leg_basico' => 'decimal:2',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->leg_nombre} {$this->leg_apellido}");
    }

    public function estaActivo(): bool
    {
        $egreso = $this->leg_fecegr;
        if ($egreso === null) {
            return true;
        }

        $reingreso = $this->leg_fecingr;

        return $reingreso !== null && $reingreso->greaterThan($egreso);
    }
}

