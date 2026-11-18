<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->string('category', 40)->nullable()->after('excerpt');
        });

        // Starter categories for the existing guides (editable in Admin > Travel guides).
        foreach ([
            'first-time-in-germany-fairytale-towns' => 'Self Drive', 'saxon-switzerland-bastei-guide' => 'Self Drive',
            'self-drive-or-chauffeur-germany' => 'Chauffeur Driven', 'cochem-mosel-castles-and-wine' => 'Chauffeur Driven',
            'german-alps-and-castles-planning' => 'Coach Tours', 'hiddensee-car-free-baltic-island' => 'Coach Tours',
        ] as $slug => $cat) {
            DB::table('posts')->where('slug', $slug)->whereNull('category')->update(['category' => $cat]);
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->dropColumn('category');
        });
    }
};
