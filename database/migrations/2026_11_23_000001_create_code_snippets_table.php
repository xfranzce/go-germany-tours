<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_snippets', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->default('script');       // script | style | html
            $t->longText('code');
            $t->string('position')->default('head');     // head | body_start | body_end
            $t->string('scope')->default('all');         // all | specific
            $t->text('pages')->nullable();                // one path per line (specific scope); * is a wildcard
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_snippets');
    }
};
