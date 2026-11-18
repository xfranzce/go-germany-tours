<?php

use Database\Seeders\MoreBlogPostsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new MoreBlogPostsSeeder)->run();
    }

    public function down(): void {}
};
