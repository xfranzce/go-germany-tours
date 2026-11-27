<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A testimonial can be about one destination, tour type and/or travel style. Those pages show their own reviews;
 * a review with none of them set is "general" and shows on the home, packages and overview pages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $t) {
            $t->foreignId('destination_id')->nullable()->after('source')->constrained()->nullOnDelete();
            $t->foreignId('tour_type_id')->nullable()->after('destination_id')->constrained()->nullOnDelete();
            $t->foreignId('travel_style_id')->nullable()->after('tour_type_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $t) {
            $t->dropConstrainedForeignId('travel_style_id');
            $t->dropConstrainedForeignId('tour_type_id');
            $t->dropConstrainedForeignId('destination_id');
        });
    }
};
