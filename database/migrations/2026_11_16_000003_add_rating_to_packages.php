<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $t) {
            $t->decimal('rating', 2, 1)->nullable()->after('price_was');      // 0.0 to 5.0, REAL guest rating only
            $t->unsignedInteger('review_count')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('packages', fn (Blueprint $t) => $t->dropColumn(['rating', 'review_count']));
    }
};
