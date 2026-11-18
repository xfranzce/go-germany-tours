<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $t) {
            $t->text('profile')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $t) {
            $t->dropColumn('profile');
        });
    }
};
