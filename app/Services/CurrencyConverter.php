<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class CurrencyConverter
{
    public const SUPPORTED_CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'SGD', 'AUD', 'CAD', 'MYR'];

    /**
     * Convert an IDR amount using the latest Frankfurter reference rate.
     *
     * @return array{amount: float, rate: float, from: string, to: string, rate_date: string}
     */
    public function convertFromRupiah(float $amount, string $currency): array
    {
        if (! in_array($currency, self::SUPPORTED_CURRENCIES, true)) {
            throw new \InvalidArgumentException('Unsupported target currency.');
        }

        $rateData = Cache::remember(
            'frankfurter.idr.'.$currency,
            now()->addHour(),
            function () use ($currency): array {
                $data = Http::acceptJson()
                    ->timeout(8)
                    ->get('https://api.frankfurter.dev/v1/latest', [
                        'base' => 'IDR',
                        'symbols' => $currency,
                    ])
                    ->throw()
                    ->json();

                $rate = data_get($data, 'rates.'.$currency);
                $date = data_get($data, 'date');
                if (! is_numeric($rate) || ! is_string($date)) {
                    throw new UnexpectedValueException('Frankfurter returned an invalid exchange rate response.');
                }

                return ['rate' => (float) $rate, 'date' => $date];
            },
        );

        return [
            'amount' => round($amount * $rateData['rate'], 2),
            'rate' => $rateData['rate'],
            'from' => 'IDR',
            'to' => $currency,
            'rate_date' => $rateData['date'],
        ];
    }
}
