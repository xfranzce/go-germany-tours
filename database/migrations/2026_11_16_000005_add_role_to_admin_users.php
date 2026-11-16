<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $t) {
            $t->string('role', 20)->default('admin')->after('password'); // admin | editor
        });
    }

    public function down(): void
    {
        Schema::table('admin_users', fn (Blueprint $t) => $t->dropColumn('role'));
    }
};
