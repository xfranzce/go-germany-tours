<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Extra destinations a package is featured on (its main destination stays packages.destination_id). Ticked on the package form. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_package', function (Blueprint $t) {
            $t->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->primary(['destination_id', 'package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_package');
    }
};
