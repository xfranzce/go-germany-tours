<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
    }

    public function test_sitemap_lists_public_pages_and_excludes_private_ones(): void
    {
        $draft = Package::first();
        $draft->update(['is_published' => false]);
        $hidden = Package::skip(1)->first();
        $hidden->update(['noindex' => true]);
        $listed = Package::where('is_published', true)->where('noindex', false)->first();

        $res = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $res->headers->get('Content-Type'));
        $xml = $res->getContent();

        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('<loc>'.url('/').'</loc>', $xml);
        $this->assertStringContainsString(route('packages.index'), $xml);
        $this->assertStringContainsString(route('destinations.show', 'cochem'), $xml);
        $this->assertStringContainsString($listed->url(), $xml);
        $this->assertStringNotContainsString($draft->url(), $xml);   // unpublished
        $this->assertStringNotContainsString($hidden->url(), $xml);  // noindex
        $this->assertStringNotContainsString('thank-you', $xml);
        $this->assertStringNotContainsString('/admin', $xml);
        simplexml_load_string($xml) ?: $this->fail('sitemap is not valid XML');
    }

    public function test_robots_blocks_admin_and_thank_you_and_points_to_the_sitemap(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /thank-you', $body);
        $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $body);
    }

    public function test_every_public_page_renders_with_one_h1_title_description_and_canonical(): void
    {
        $urls = ['/', '/packages', '/about', '/contact', '/terms-and-conditions', '/privacy-policy', '/guides', '/photo-credits', '/meet-our-team', '/testimonials', '/faqs',
            '/destinations/quedlinburg', '/tours/self-drive', Package::first()->url()];

        foreach ($urls as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html), "{$url} must have exactly one H1");
            $this->assertMatchesRegularExpression('/<title>[^<]{5,}<\/title>/', $html, $url);
            $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{20,}"/', $html, $url);
            $this->assertMatchesRegularExpression('/<link rel="canonical" href="[^"]+"/', $html, $url);
        }
    }

    public function test_header_has_the_tours_and_about_us_mega_menus(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('id="mega-tours"', $html);
        $this->assertStringContainsString('id="mega-about"', $html);
        foreach (['Self Drive', 'Chauffeur Driven', 'Coach Tours'] as $type) {
            $this->assertStringContainsString($type, $html);
        }
        foreach (['/about', '/meet-our-team', '/testimonials', '/faqs'] as $path) {
            $this->assertStringContainsString($path, $html);
        }
        $this->assertStringContainsString(Package::first()->url(), $html, 'tour mega menu lists packages');
        $this->get('/faqs')->assertOk()->assertSee('"@type":"FAQPage"', false);
    }

    public function test_home_has_faq_structured_data_and_the_hero_quiz_without_a_destination_dropdown(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('"@type":"TravelAgency"', $html);
        $this->assertStringContainsString('id="quiz-title"', $html); // the quote quiz sits in the hero
        $this->assertStringNotContainsString('id="hero-dest"', $html); // destination dropdown and ticker were removed
        $this->assertStringNotContainsString('Destinations we cover', $html);
        $this->assertStringNotContainsString('googletagmanager.com/gtm.js', $html); // GTM off until an ID is set
    }

    public function test_gtm_snippet_only_appears_when_an_id_is_set(): void
    {
        \App\Models\Setting::put('gtm_id', 'GTM-ABC1234');
        $this->get('/')->assertSee('GTM-ABC1234', false)->assertSee('googletagmanager.com/gtm.js', false);
    }

    public function test_unknown_pages_show_the_custom_404_and_unpublished_packages_are_hidden(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('We could not find that page');
        $p = Package::first();
        $p->update(['is_published' => false]);
        $this->get($p->url())->assertNotFound();
    }

    public function test_package_filters_work_and_filtered_views_are_noindex(): void
    {
        $html = $this->get('/packages?type=coach-tours')->assertOk()->assertSee('noindex', false)->getContent();
        // Check the results area only: the header's Tours mega menu intentionally lists every package.
        preg_match('/<main.*<\/main>/s', $html, $m);
        $this->assertStringContainsString('Mosel Valley', $m[0]);
        $this->assertStringNotContainsString('Hiddensee Baltic Island Escape', $m[0]);
        $this->get('/packages')->assertOk()->assertDontSee('content="noindex', false);
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
