<?php

use Illuminate\Database\Migrations\Migration;

/** Every package gets its own cover photo (UniquePackageImagesSeeder; admin-uploaded galleries are left alone). */
return new class extends Migration
{
    public function up(): void
    {
        (new \Database\Seeders\UniquePackageImagesSeeder)->run();
    }

    public function down(): void
    {
    }
};
