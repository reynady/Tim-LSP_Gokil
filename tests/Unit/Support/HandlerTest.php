<?php

namespace Tests\Unit\Support;

use App\Support\Handler;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function test_it_converts_rupiah_to_usd_at_sixteen_thousand_rupiah_per_dollar(): void
    {
        $this->assertSame(1.0, Handler::convertRupiahToUsd(16_000));
        $this->assertSame(2.0, Handler::convertRupiahToUsd(32_000));
        $this->assertSame(5.0, Handler::convertRupiahToUsd(80_000));
    }
}
