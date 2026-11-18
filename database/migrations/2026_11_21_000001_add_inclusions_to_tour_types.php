<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->text('inclusions')->nullable()->after('highlights');
        });

        $lists = [
            'self-drive' => "Premium Rental Car\nFlexible Pace\n3-4 Star Hotels\nCurated Activity",
            'coach-tours' => "Coach-Guided\nExclusive Tours\n3-4 Star Hotels\nCurated Activity",
            'chauffeur-driven' => "Chauffeur-Guided\nExclusive Tours\n3-4 Star Hotels\nCurated Activity",
        ];
        foreach ($lists as $slug => $text) {
            DB::table('tour_types')->where('slug', $slug)->update(['inclusions' => $text]);
        }
    }

    public function down(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->dropColumn('inclusions');
        });
    }
};
