<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Gives the starter (SAMPLE) blog posts a featured image and adds a third sample post so the home
 * grid has three cards. Only fills images that are empty, so real uploads are never replaced.
 * Run: php artisan db:seed --class=BlogImagesSeeder (safe to repeat).
 */
class BlogImagesSeeder extends Seeder
{
    public function run(): void
    {
        Post::firstOrCreate(['slug' => 'german-alps-and-castles-planning'], [
            'title' => 'Castles and the Alps: planning a Bavarian trip',
            'excerpt' => 'What to think about when you combine fairytale castles with mountain scenery.',
            'body' => '<p>[SAMPLE ARTICLE: replace with real content] Bavaria combines castles, lakes and Alpine views. Think about how much driving you want, which season suits you and how many nights you would like in each base.</p>',
            'is_published' => true, 'published_at' => now()->subDays(2),
            'seo_title' => 'Castles and the Alps: planning a Bavarian trip',
            'meta_description' => 'What to think about when you combine fairytale castles with mountain scenery.',
        ]);

        $images = [
            'first-time-in-germany-fairytale-towns' => 'quedlinburg-lane',
            'self-drive-or-chauffeur-germany' => 'mosel-vineyard',
            'german-alps-and-castles-planning' => 'neuschwanstein-castle',
        ];
        foreach ($images as $slug => $stem) {
            $post = Post::where('slug', $slug)->first();
            $media = Media::where('path', 'like', "media/{$stem}-%")->first();
            if ($post && $media && ! $post->image) {
                $post->update(['image' => $media->path]);
            }
        }
    }
}
