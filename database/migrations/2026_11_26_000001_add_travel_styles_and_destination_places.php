<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Travel styles (Slow, Scenic, Luxury, Food & Wine, Cultural & Heritage, Adventure), linked to packages many-to-many,
 * and a tagline + highlight places for each destination (the /destinations overview, like goeurotours.com).
 * Starter rows are only added when missing, so this is safe on the live site; everything is editable in the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_styles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('tagline')->nullable();          // small eyebrow, e.g. "Breathtaking views"
            $t->string('summary', 500)->nullable();
            $t->longText('description')->nullable();
            $t->text('highlights')->nullable();          // "What to expect", one per line
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->timestamps();
        });

        Schema::create('package_travel_style', function (Blueprint $t) {
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->foreignId('travel_style_id')->constrained()->cascadeOnDelete();
            $t->primary(['package_id', 'travel_style_id']);
        });

        Schema::table('destinations', function (Blueprint $t) {
            $t->string('tagline')->nullable()->after('name');   // e.g. "Half-timbered towns & Harz mountain views"
            $t->text('places')->nullable()->after('summary');   // highlight places, one per line: "Wernigerode: castle and colourful old town"
        });

        (new \Database\Seeders\TravelStyleSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('package_travel_style');
        Schema::dropIfExists('travel_styles');
        Schema::table('destinations', fn (Blueprint $t) => $t->dropColumn(['tagline', 'places']));
    }
};
