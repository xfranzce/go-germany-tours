<?php

use Database\Seeders\TourTypeFaqSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $t) {
            $t->foreignId('tour_type_id')->nullable()->after('package_id')->constrained()->nullOnDelete();
        });

        // Existing sites get the starter tour-type FAQs on deploy (no-op when there are no tour types yet).
        (new TourTypeFaqSeeder)->run();
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $t) {
            $t->dropConstrainedForeignId('tour_type_id');
        });
    }
};
