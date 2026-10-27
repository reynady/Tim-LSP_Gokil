<?php

namespace App\Support;

class Handler
{
    public const RUPIAH_PER_USD = 16_000;

    public static function convertRupiahToUsd(int|float $rupiah): float
    {
        return round($rupiah / self::RUPIAH_PER_USD, 2);
    }
}
