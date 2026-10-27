<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_convert_rupiah_to_usd_with_frankfurter(): void
    {
        Cache::forget('frankfurter.idr.USD');
        Http::fake([
            'https://api.frankfurter.dev/v1/latest*' => Http::response([
                'amount' => 1,
                'base' => 'IDR',
                'date' => '2026-10-26',
                'rates' => ['USD' => 0.000055059],
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create(['role' => 'member']))
            ->getJson(route('currency.convert', ['amount' => 25_000, 'currency' => 'USD']));

        $response->assertOk()
            ->assertJson([
                'amount' => 1.38,
                'rate' => 0.000055059,
                'from' => 'IDR',
                'to' => 'USD',
                'rate_date' => '2026-10-26',
            ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.frankfurter.dev/v1/latest'));
    }

    public function test_guest_cannot_use_currency_conversion_endpoint(): void
    {
        $this->getJson(route('currency.convert', ['amount' => 25_000, 'currency' => 'USD']))
            ->assertUnauthorized();
    }

    public function test_unsupported_currency_is_rejected_without_calling_frankfurter(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson(route('currency.convert', ['amount' => 25_000, 'currency' => 'XYZ']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency');

        Http::assertNothingSent();
    }

    public function test_rate_provider_failure_returns_clear_service_unavailable_response(): void
    {
        Http::fake([
            'https://api.frankfurter.dev/v1/latest*' => Http::response([], 503),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'member']))
            ->getJson(route('currency.convert', ['amount' => 25_000, 'currency' => 'EUR']))
            ->assertServiceUnavailable()
            ->assertJson([
                'message' => 'Kurs mata uang tidak dapat diambil saat ini. Silakan coba lagi.',
            ]);
    }
}
