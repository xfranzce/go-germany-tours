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

    public function test_from_is_only_shown_for_a_plain_starting_price(): void
    {
        $this->assertSame('From $3,290 pp', $this->label(3290, null));
        $this->assertSame('$3,290 pp', $this->label(3290, 3590));   // sale: higher "from" price is crossed out elsewhere
        $this->assertSame('$3,290 pp', $this->label(3290, 3000));   // entered price is lower than the current one: no "From"
        $this->assertSame('$3,290 pp', $this->label(3290, 3290));
        $this->assertSame('Price on request', $this->label(null, null));
    }
}
