<?php

use Illuminate\Database\Migrations\Migration;

/** Tops each destination up to five sample tours (see FiveToursPerDestinationSeeder; never overwrites existing packages). */
return new class extends Migration
{
    public function up(): void
    {
        (new \Database\Seeders\FiveToursPerDestinationSeeder)->run();
    }

    public function down(): void
    {
        // sample content: remove in Admin > Packages if not wanted
    }
};
