<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_uses_the_left_text_right_quiz_hero_and_the_test_page_the_centred_one(): void
    {
        $this->seedContent();

        $home = $this->get('/')->assertOk()->assertSee('The Germany Collection')->assertDontSee('Find the best value vacation')->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $home));
        $this->assertStringContainsString('id="quiz-title"', $home);   // the quote quiz sits in the hero

        $test = $this->get('/test-home')->assertOk()->assertSee('Find the best value vacation')->assertDontSee('The Germany Collection')->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $test));
        $this->assertStringContainsString('noindex', $test);
    }

    public function test_hero_section_page_is_an_unlisted_copy_of_the_home_hero(): void
    {
        $this->seedContent();

        $page = $this->get('/hero-section')->assertOk()->assertSee('The Germany Collection')->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $page));
        $this->assertStringContainsString('id="quiz-title"', $page);
        $this->assertStringContainsString('noindex', $page);
        $this->assertStringNotContainsString('/hero-section', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('Disallow: /hero-section', $this->get('/robots.txt')->getContent());
    }
}