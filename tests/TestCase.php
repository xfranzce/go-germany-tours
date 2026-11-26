<?php

namespace Tests;

use App\Models\Setting;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ContentSeeder;
use Database\Seeders\TravelStyleSeeder;
use Database\Seeders\PagesSeeder;
use Database\Seeders\QuizSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Setting::flush(); // settings are memoised per process
    }

    /** Seed content without the (slow) image import. */
    protected function seedContent(): void
    {
        $this->seed([PagesSeeder::class, CatalogSeeder::class, QuizSeeder::class, ContentSeeder::class, TravelStyleSeeder::class]);
    }
}
