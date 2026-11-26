<?php

use Illuminate\Database\Migrations\Migration;

/** Overview, highlights with photos, day-by-day itinerary and inclusions for the sample packages (PackageContentSeeder; placeholders only). */
return new class extends Migration
{
    public function up(): void
    {
        (new \Database\Seeders\PackageContentSeeder)->run();
    }

    public function down(): void
    {
    }
};
