<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Adds the "Meet Our Team" page (placeholder content) without touching any page you have already edited.
 * Run: php artisan db:seed --class=AboutMenuPagesSeeder (safe to repeat).
 */
class AboutMenuPagesSeeder extends Seeder
{
    public function run(): void
    {
        if (Page::where('key', 'team')->exists()) {
            return;
        }
        Page::create([
            'key' => 'team', 'title' => 'Meet Our Team', 'data' => [],
            'content' => '<p>[CONFIRM: introduce the team. Add the real name, role and a short bio of each person, ideally with a photo, here in Admin > Pages > Meet Our Team.]</p><h2>[PLACEHOLDER: Team member name]</h2><p><em>[PLACEHOLDER: role]</em></p><p>[PLACEHOLDER: short bio]</p><h2>[PLACEHOLDER: Team member name]</h2><p><em>[PLACEHOLDER: role]</em></p><p>[PLACEHOLDER: short bio]</p><h2>[PLACEHOLDER: Team member name]</h2><p><em>[PLACEHOLDER: role]</em></p><p>[PLACEHOLDER: short bio]</p>',
            'seo_title' => 'Meet Our Team | Go Germany Tours',
            'meta_description' => 'Meet the people who plan your Germany vacation at Go Germany Tours.',
        ]);
    }
}
