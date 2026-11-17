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
            $t->text('highlights')->nullable()->after('description');
        });

        // Starter lists (draft copy; the [CONFIRM] lines need the real details). Edit in Admin > Tour types.
        $starter = [
            'self-drive' => ["Pre-booked accommodation\nA suggested route with day-by-day notes\nLocal tips for every stop\nYou drive at your own pace\n[CONFIRM: car hire, luggage and support during the trip]"],
            'chauffeur-driven' => ["A private driver between towns\nRoads, parking and timings handled for you\nStays and route planned around you\nTime to enjoy the scenery and the wine\n[CONFIRM: driver and vehicle details]"],
            'coach-tours' => ["A guided small-group itinerary\nLogistics taken care of\nTravel alongside other guests\nThe highlights of each region in one trip\n[CONFIRM: group size, guide and inclusions]"],
        ];
        foreach ($starter as $slug => [$text]) {
            DB::table('tour_types')->where('slug', $slug)->whereNull('highlights')->update(['highlights' => $text]);
        }
    }

    public function down(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->dropColumn('highlights');
        });
    }
};
