<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $t) {
            $t->string('sheet_status')->default('off');   // off | pending | synced | failed
            $t->text('sheet_error')->nullable();
            $t->timestamp('sheet_synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $t) {
            $t->dropColumn(['sheet_status', 'sheet_error', 'sheet_synced_at']);
        });
    }
};
