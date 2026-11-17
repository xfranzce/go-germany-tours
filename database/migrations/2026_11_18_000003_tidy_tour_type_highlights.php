<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Drops the starter [CONFIRM] line so each list is 4 clean points (only rows still holding the starter text). */
    public function up(): void
    {
        foreach (DB::table('tour_types')->where('highlights', 'like', '%[CONFIRM%')->get(['id', 'highlights']) as $r) {
            $lines = array_filter(array_map('trim', preg_split('/\R/', $r->highlights)), fn ($l) => $l !== '' && ! str_contains($l, '[CONFIRM'));
            DB::table('tour_types')->where('id', $r->id)->update(['highlights' => implode("\n", $lines)]);
        }
    }

    public function down(): void {}
};
