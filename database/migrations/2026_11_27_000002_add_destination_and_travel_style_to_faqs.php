<?php

use Database\Seeders\PageFaqSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FAQs can belong to one destination or travel style page (like tour_type_id); then seed starter FAQs for each. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $t) {
            $t->foreignId('destination_id')->nullable()->after('tour_type_id')->constrained()->nullOnDelete();
            $t->foreignId('travel_style_id')->nullable()->after('destination_id')->constrained()->nullOnDelete();
        });

        (new PageFaqSeeder)->run();
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $t) {
            $t->dropConstrainedForeignId('travel_style_id');
            $t->dropConstrainedForeignId('destination_id');
        });
    }
};
