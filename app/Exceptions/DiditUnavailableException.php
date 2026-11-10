<?php

namespace App\Exceptions;

use RuntimeException;

class DiditUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'El servicio de verificación de identidad no está disponible temporalmente. Reintentá en unos minutos.')
    {
        parent::__construct($message, 503);
    }
}

