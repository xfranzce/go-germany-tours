<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $t) {
            $t->string('source')->nullable()->after('location'); // e.g. "Google review, March 2025"
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', fn (Blueprint $t) => $t->dropColumn('source'));
    }
};
