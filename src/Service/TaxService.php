<?php

namespace App\Service;

class TaxService
{
    const TAX_RATES = [
        'DE' => 0.19,
        'IT' => 0.22,
        'FR' => 0.20,
        'GR' => 0.24,
    ];

    public static function calculateTax(float $amount, string $countryCode): float
    {
        if (!isset(self::TAX_RATES[$countryCode])) {
            throw new \InvalidArgumentException('Неизвестный код страны');
        }
        return $amount * self::TAX_RATES[$countryCode];
    }
}