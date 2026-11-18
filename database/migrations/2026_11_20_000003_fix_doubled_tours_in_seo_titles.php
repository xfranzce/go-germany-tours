<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** "Coach Tours Tours of Germany" -> "Coach Tours of Germany" (only the exact doubled wording is touched). */
    public function up(): void
    {
        foreach (DB::table('tour_types')->where('seo_title', 'like', '% Tours Tours of Germany')->get(['id', 'seo_title']) as $t) {
            DB::table('tour_types')->where('id', $t->id)->update(['seo_title' => str_replace('Tours Tours of Germany', 'Tours of Germany', $t->seo_title)]);
        }
    }

    public function down(): void {}
};
