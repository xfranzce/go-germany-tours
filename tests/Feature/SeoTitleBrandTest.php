<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PostController;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SeoTitleBrandTest extends TestCase
{
    use RefreshDatabase;

    private function cleaned(?string $title): ?string
    {
        Setting::flush();
        $request = Request::create('/x', 'POST', ['seo_title' => $title]);
        $call = fn () => $this->stripBrandFromSeoTitle($request);
        $call->call(new PostController);

        return $request->input('seo_title');
    }

    public function test_the_site_name_is_removed_from_an_seo_title_so_it_fits_and_is_not_doubled(): void
    {
        $brand = Setting::get('brand_name');
        $this->assertSame('Saxon Switzerland and Dresden Small-Group Coach Tour', $this->cleaned("Saxon Switzerland and Dresden Small-Group Coach Tour | {$brand}"));
        $this->assertSame('Quedlinburg tours', $this->cleaned("Quedlinburg tours - {$brand}"));
        $this->assertSame('A title without the name', $this->cleaned('A title without the name'));
        $this->assertSame("About {$brand} in Germany", $this->cleaned("About {$brand} in Germany"));
        $this->assertNull($this->cleaned(null));
    }
}
