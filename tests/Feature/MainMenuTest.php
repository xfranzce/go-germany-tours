<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_has_home_first_and_packages_only_inside_the_tours_menu(): void
    {
        $this->seedContent();
        $html = $this->get('/about')->assertOk()->getContent();

        // desktop nav: Home comes before Tours, and there is no stand-alone "Packages" item any more
        $desktop = substr($html, strpos($html, 'aria-label="Main"'), 6000);
        $this->assertLessThan(strpos($desktop, '>Tours</a>'), strpos($desktop, '>Home</a>'));
        // the "Tours" label itself is a link to the Packages page
        $this->assertMatchesRegularExpression('#<a href="' . preg_quote(route('packages.index'), '#') . '"[^>]*>Tours</a>#', $desktop);
        $this->assertStringNotContainsString('hover:bg-current/10">Packages</a>', $html);

        // mobile menu: same
        $this->assertMatchesRegularExpression('#id="mobile-menu".*?>Home</a>#s', $html);
        $this->assertDoesNotMatchRegularExpression('#py-3\.5 text-sm uppercase tracking-wider" href="[^"]*/packages">Packages</a>#', $html);

        // the packages page is linked from inside the Tours menu (desktop bar and mobile list)
        $this->assertStringContainsString(route('packages.index'), substr($html, strpos($html, 'id="m-tours"'), 6000));
        $this->assertStringContainsString(route('packages.index'), substr($html, strpos($html, 'id="mega-tours"'), 9000));
    }
}
