# Image credits

All photographs come from **Wikimedia Commons** and were downloaded into `database/seed-images/` (never hot-linked). The seeder converts them to responsive WebP in `storage/app/public/media`. The same data is shown on the public **Photo credits** page (`/photo-credits`, linked in the footer), which is what the CC BY licences require (credit the author, link the licence).

Licence check done on 2026-11-11 from the Commons file metadata. Re-check before launch if you want to be certain.

| File | Source | Photographer | Licence | Used where |
|---|---|---|---|---|
| `hero-quedlinburg.jpg` | [Quedlinburg, Schlossberg (November 2022).jpg](https://commons.wikimedia.org/wiki/File:Quedlinburg,_Schlossberg_(November_2022).jpg) | Romzig | [CC0](http://creativecommons.org/publicdomain/zero/1.0/deed.en) | Home hero background; Quedlinburg and Fairytale Towns packages; default social share image |
| `quedlinburg-lane.jpg` | [Quedlinburg, Schlossberg (November 2022) 5.jpg](https://commons.wikimedia.org/wiki/File:Quedlinburg,_Schlossberg_(November_2022)_5.jpg) | Romzig | [CC0](http://creativecommons.org/publicdomain/zero/1.0/deed.en) | Quedlinburg destination card/page; Quedlinburg and Fairytale Towns package galleries |
| `saxon-switzerland-bastei.jpg` | [Felsenfestung Bastei Saechsische Schweiz.jpg](https://commons.wikimedia.org/wiki/File:Felsenfestung_Bastei_Saechsische_Schweiz.jpg) | S.Rose Fotografie | [CC BY 4.0](https://creativecommons.org/licenses/by/4.0) | Saxon Switzerland destination; Saxon Switzerland package |
| `cochem-mosel.jpg` | [20190813Cochem2.jpg](https://commons.wikimedia.org/wiki/File:20190813Cochem2.jpg) | AnRo0002 | [CC0](http://creativecommons.org/publicdomain/zero/1.0/deed.en) | Cochem destination; Mosel Valley package; Coach Tours card |
| `mosel-vineyard.jpg` | [Mosel im Landkreis Trier-Saarburg-36-Weinberge-2013-gje.jpg](https://commons.wikimedia.org/wiki/File:Mosel_im_Landkreis_Trier-Saarburg-36-Weinberge-2013-gje.jpg) | Gerd Eichmann | [CC BY 4.0](https://creativecommons.org/licenses/by/4.0) | Mosel Valley package gallery; Self Drive card |
| `hiddensee-lighthouse.jpg` | [Ostseeinsel Hiddensee - Leuchtturm auf dem Dornbusch - panoramio.jpg](https://commons.wikimedia.org/wiki/File:Ostseeinsel_Hiddensee_-_Leuchtturm_auf_dem_Dornbusch_-_panoramio.jpg) | Gottfried Hoffmann | [CC BY 3.0](https://creativecommons.org/licenses/by/3.0) | Hiddensee destination; Hiddensee package |
| `hiddensee-beach.jpg` | [Hiddensee - Flickr - abbilder (1).jpg](https://commons.wikimedia.org/wiki/File:Hiddensee_-_Flickr_-_abbilder_(1).jpg) | abbilder | [CC BY 4.0](https://creativecommons.org/licenses/by/4.0) | Hiddensee package gallery |
| `gengenbach-rathaus.jpg` | [Gengenbach-Rathaus-06-2025-gje.jpg](https://commons.wikimedia.org/wiki/File:Gengenbach-Rathaus-06-2025-gje.jpg) | Gerd Eichmann | [CC BY 4.0](https://creativecommons.org/licenses/by/4.0) | Gengenbach destination; Black Forest package; Chauffeur Driven card; Fairytale Towns gallery |
| `neuschwanstein-castle.jpg` | [Neuschwanstein Castle (532850).jpg](https://commons.wikimedia.org/wiki/File:Neuschwanstein_Castle_(532850).jpg) | cameralends | [CC0](http://creativecommons.org/publicdomain/zero/1.0/deed.en) | Home "Why book" arch photo |
| `bacharach-rhine.jpg` | [Blick auf Bacharach und den Rhein - Flickr - abbilder.jpg](https://commons.wikimedia.org/wiki/File:Blick_auf_Bacharach_und_den_Rhein_-_Flickr_-_abbilder.jpg) | abbilder | [CC BY 2.0](https://creativecommons.org/licenses/by/2.0) | Home "Why book" second photo |
| `allgau-alps.jpg` | [Allgäu alps autumn 2017-10-13.jpg](https://commons.wikimedia.org/wiki/File:Allg%C3%A4u_alps_autumn_2017-10-13.jpg) | Tommy Rau | [CC0](http://creativecommons.org/publicdomain/zero/1.0/deed.en) | Home Alps statement banner |

## AI-generated illustrations
Four more "By traveler" card images (`couple-rhine-vineyard`, `family-old-town`, `medieval-castle-path`, `anniversary-dinner-terrace`) are AI-generated the same way. The three tour-type card images (`selfdrive-road.jpg`, `chauffeur-private-driver.jpg`, `coach-tour-cochem.jpg`) are AI-generated (Higgsfield, Nano Banana 2) illustrations, not photographs of real trips, and are labelled as such on the Photo credits page. They show a generic car, chauffeur and coach. Replace with your own photos when you have them (Admin > Tour types).

## Notes

- **CC0** = public-domain dedication: no attribution needed (we credit anyway).
- **CC BY 3.0 / 4.0** = free for commercial use **if you credit the photographer and link the licence**. The credits page does this. Do not remove it while these images are live.
- You asked for Unsplash, Pexels or Pixabay, public domain, or your own images. Wikimedia Commons CC0/CC BY is a fourth source: please confirm you are happy with it, or swap the five CC BY photos for your own or Unsplash/Pexels ones. No competitor or Google Images material was used.
- One Hiddensee candidate with a visible "Wikipedia" watermark was rejected. The Quedlinburg hero shows scaffolding on the castle; replace it if you prefer a cleaner shot.
- No images contain brand logos. Small incidental shop signs may be visible in the Gengenbach photo.

## Adding or replacing images

Upload in **Admin > Media** (or inside a package, page or settings screen). Alt text is mandatory. Then add a row to this table and to `database/seed-images/credits.json` if it needs a credit.
## Video
- `public/videos/hero.mp4`: AI-generated (Higgsfield, Seedance 2.5, image-to-video from the Neuschwanstein autumn image below; 1080p, high-bitrate, 6 s, re-encoded with ffmpeg (x264, CRF 27) to a 5 s seamless loop of about 2.2 MB, no audio; an earlier 720p / 0.5 MB version looked pixelated on large screens). The scene is an AI rendering of Neuschwanstein Castle, not real footage. Replaced the earlier AI clip of a half-timbered hillside town (still in git history). Replace with licensed real footage if preferred.
- `database/seed-images/neuschwanstein-autumn.jpg` (home hero photo): AI-generated with Google Gemini ("Neuschwanstein Castle in golden autumn light"), shrunk to 1920 px wide; imported as WebP by `images:import-seed` and labelled as AI-generated on /photo-credits.
- `public/videos/banner.mp4`: AI-generated (Higgsfield, Seedance 2.5, prompt: aerial flight over autumn Bavarian Alps with drifting clouds). Plays over the Alps banner photo.

New seed photos are imported on deploy by `php artisan images:import-seed` (add the file to `database/seed-images`, an entry to `ImageSeeder::ALT` and `credits.json`). Originals are stored at max 3840px wide.
