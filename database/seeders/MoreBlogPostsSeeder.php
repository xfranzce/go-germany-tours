<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Database\Seeder;

/** Three general destination guides (no prices or business claims). Safe to repeat; never overwrites edits. */
class MoreBlogPostsSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'saxon-switzerland-bastei-guide', 'stem' => 'saxon-switzerland-bastei', 'days' => 4,
                'title' => 'Saxon Switzerland: rocks, river views and the Bastei',
                'excerpt' => 'Sandstone towers, forest trails and the Elbe far below: what to expect in one of eastern Germany\'s most dramatic landscapes.',
                'body' => '<p>Close to Dresden, along the Elbe near the Czech border, the Elbe Sandstone Mountains rise in pillars, ridges and deep forested gorges. The area is known as Saxon Switzerland, and it is one of the most striking landscapes in the east of Germany.</p>'
                    .'<h2>The Bastei viewpoint</h2><p>The best known sight is the Bastei, a cluster of rock formations high above the Elbe, with a stone bridge linking several of the towers. Walk out early or late in the day if you can, because the viewing platforms are at their calmest then and the light on the river is at its best.</p>'
                    .'<h2>Ways to explore</h2><ul><li><strong>On foot:</strong> marked trails range from short strolls to full-day hikes through the national park.</li><li><strong>By river:</strong> historic paddle steamers and local boats run along the Elbe, which gives you a different view of the cliffs.</li><li><strong>By fortress:</strong> Königstein Fortress sits on a plateau above the river and is worth including if you enjoy history.</li></ul>'
                    .'<h2>Planning tips</h2><p>Spring and autumn are comfortable for walking, and summer is the busiest. Wear sturdy shoes, because steps and uneven paths are part of most viewpoints. Allow two nights if you want to see the area without rushing, and combine it with a stay in Dresden for city sights, museums and food.</p>'
                    .'<p>Tell us your dates and how much walking you enjoy, and we can shape a route that fits you.</p>',
            ],
            [
                'slug' => 'hiddensee-car-free-baltic-island', 'stem' => 'hiddensee-lighthouse', 'days' => 5,
                'title' => 'Hiddensee: a quiet, car-free island on the Baltic',
                'excerpt' => 'Lighthouse walks, sandy beaches and horse-drawn carriages: why this slow little island suits a relaxed Germany trip.',
                'body' => '<p>Hiddensee is a long, narrow island in the Baltic Sea, just off the larger island of Rügen. It has no private cars, so the pace is slow, the air is clean and getting around happens on foot, by bicycle or in a horse-drawn carriage.</p>'
                    .'<h2>Getting there</h2><p>Ferries run from the mainland and from Rügen, and the crossing is part of the experience. Because you leave the car behind on the mainland, it is worth planning your arrival and departure times in advance, especially in busy months.</p>'
                    .'<h2>What to see</h2><ul><li><strong>The Dornbusch lighthouse:</strong> a short walk or ride from the village of Kloster, with wide views over the island and the sea.</li><li><strong>Kloster:</strong> a pretty village of thatched cottages, small galleries and gardens.</li><li><strong>The beaches:</strong> long stretches of sand and dunes on the western side, ideal for a quiet walk.</li></ul>'
                    .'<h2>Who it suits</h2><p>Hiddensee is a good choice if you like walking, cycling and unhurried days. It is a place to slow down rather than tick off sights, so a night or two is enough for many travelers, and longer if you want to simply relax.</p>'
                    .'<p>Send us your dates and we can fit the island into a wider trip along the northern coast.</p>',
            ],
            [
                'slug' => 'cochem-mosel-castles-and-wine', 'stem' => 'cochem-mosel', 'days' => 6,
                'title' => 'Cochem and the Mosel: castles, vineyards and slow travel',
                'excerpt' => 'A river valley of steep vineyards, half-timbered towns and hilltop castles, and how to enjoy it at an easy pace.',
                'body' => '<p>The Mosel winds through western Germany in tight loops, with steep vineyard slopes on both banks and small towns tucked along the water. Cochem is one of the best known, with a castle above the roofs and a pretty old town below it.</p>'
                    .'<h2>Castle and old town</h2><p>Reichsburg Cochem stands on a hill above the town and can be reached on foot through the old streets. Below it, narrow lanes, squares and half-timbered houses are easy to explore in an afternoon.</p>'
                    .'<h2>Wine country</h2><p>The Mosel is famous for Riesling, grown on some of the steepest vineyards in Europe. Many family wineries welcome visitors for tastings, and a riverside walk or a short boat trip is a relaxed way to take in the slopes.</p>'
                    .'<h2>Getting around</h2><ul><li><strong>Self drive:</strong> the river road is scenic, with plenty of places to stop.</li><li><strong>Chauffeur:</strong> ideal if you would like to enjoy a tasting without worrying about the drive.</li><li><strong>By boat:</strong> excursion boats connect several riverside towns in season.</li></ul>'
                    .'<p>Early autumn, when the grapes are ripening, is a popular time to visit. Tell us the season you prefer and we can plan the stays and the route around you.</p>',
            ],
        ];

        foreach ($posts as $p) {
            $image = Media::where('path', 'like', "media/{$p['stem']}-%")->first()?->path;
            $post = Post::firstOrCreate(['slug' => $p['slug']], [
                'title' => $p['title'], 'excerpt' => $p['excerpt'], 'body' => $p['body'],
                'is_published' => true, 'published_at' => now()->subDays($p['days']),
                'seo_title' => $p['title'], 'meta_description' => $p['excerpt'], 'image' => $image,
            ]);
            if (! $post->image && $image) {
                $post->update(['image' => $image]);
            }
        }
    }
}
