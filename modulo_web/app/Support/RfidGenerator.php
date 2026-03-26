<?php

namespace App\Support;

use Illuminate\Support\Str;

class RfidGenerator
{
    /**
     * Gera uma tag RFID padronizada.
     * Inicia com 'C' e possui caracteres aleatórios (ex: C8F9A2B3D4).
     * O tamanho total é 11 (< 16 obrigatórios pelo BD).
     */
    public static function generateCattleTag(): string
    {
        return 'C' . strtoupper(Str::random(10));
    }

    /**
     * Gera uma tag RFID padronizada para Veterinários.
     * Inicia com 'V' e possui caracteres aleatórios (ex: V8F9A2B3D4).
     * O tamanho total é 11 (< 16 obrigatórios pelo BD).
     */
    public static function generateVetTag(): string
    {
        return 'V' . strtoupper(Str::random(10));
    }

    public static function isCattleTag(?string $rfid): bool
    {
        return self::isValid($rfid) && strtoupper($rfid[0]) === 'C';
    }

    /**
     * Valida se uma tag RFID é válida para o sistema.
     * Regras:
     * - Começa com 'C' ou 'V'
     * - Tamanho entre 2 e 16 caracteres
     * - Apenas caracteres alfanuméricos
     */
    public static function isValid(?string $rfid): bool
    {
        if (empty($rfid)) {
            return false;
        }

        $length = strlen($rfid);
        if ($length < 2 || $length > 16) {
            return false;
        }

        $prefix = strtoupper($rfid[0]);
        if ($prefix !== 'C' && $prefix !== 'V') {
            return false;
        }

        return ctype_alnum($rfid);
    }

    public static function isVetTag(?string $rfid): bool
    {
        return self::isValid($rfid) && strtoupper($rfid[0]) === 'V';
    }
}
