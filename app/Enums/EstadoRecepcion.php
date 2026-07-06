<?php

namespace App\Enums;

enum EstadoRecepcion: string
{
    case BORRADOR = 'BORRADOR';
    case PENDIENTE_FECHA = 'PENDIENTE_FECHA';
    case CONFIRMADO = 'CONFIRMADO';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::PENDIENTE_FECHA => 'Pendiente de Fechas',
            self::CONFIRMADO => 'Confirmado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BORRADOR => 'yellow',
            self::PENDIENTE_FECHA => 'orange',
            self::CONFIRMADO => 'green',
        };
    }
}
