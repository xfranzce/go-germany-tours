<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id')->nullable()->index();
            $table->string('user_name')->nullable();      // snapshots, so the history survives a deleted user
            $table->string('user_email')->nullable();
            $table->string('action', 30)->index();        // created, updated, deleted, trashed, restored, login, logout, login_failed
            $table->string('subject_type', 60)->nullable()->index();   // friendly area name, e.g. "Package"
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();
            $table->string('summary', 500);
            $table->json('changes')->nullable();          // {field: [old, new]}
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
