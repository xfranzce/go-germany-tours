<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD = 'Tailor-made Germany vacations for US travelers.';

    public function up(): void
    {
        // Only replaces the untouched original text; anything an administrator has edited is left alone.
        DB::table('settings')->where('key', 'footer_text')->where('value', self::OLD)
            ->update(['value' => config('gogermany.defaults.footer_text')]);
        Setting::flush();
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'footer_text')->where('value', config('gogermany.defaults.footer_text'))
            ->update(['value' => self::OLD]);
        Setting::flush();
    }
};
