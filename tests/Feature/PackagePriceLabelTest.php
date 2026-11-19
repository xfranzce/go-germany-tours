<?php

namespace Tests\Feature;

use App\Models\Package;
use PHPUnit\Framework\TestCase;

class PackagePriceLabelTest extends TestCase
{
    private function label(?float $from, ?float $was): string
    {
        return (new Package(['price_from' => $from, 'price_was' => $was, 'currency' => 'USD']))->priceLabel();
    }

    public function test_from_only_appears_on_the_crossed_out_old_price_of_a_discount(): void
    {
        $this->assertSame('$3,290 pp', $this->label(3290, null));   // no discount: plain price, no "From"
        $this->assertSame('$3,290 pp', $this->label(3290, 3590));   // discount: current price is plain
        $this->assertSame('$3,290 pp', $this->label(3290, 3000));   // entered price not higher: no discount, no "From"
        $this->assertSame('Price on request', $this->label(null, null));

        $was = fn (?float $from, ?float $was) => (new Package(['price_from' => $from, 'price_was' => $was, 'currency' => 'USD']))->wasLabel();
        $this->assertSame('From $3,590 pp', $was(3290, 3590));      // the only place "From" shows
        $this->assertNull($was(3290, null));
        $this->assertNull($was(3290, 3000));
    }
}
