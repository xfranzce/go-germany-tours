<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('role', 120)->nullable();
            $t->text('bio')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // Six placeholder cards: never invent real people. Edit them in Admin > Team.
        for ($i = 1; $i <= 6; $i++) {
            DB::table('team_members')->insert([
                'name' => '[PLACEHOLDER: Team member name]', 'role' => '[PLACEHOLDER: role]',
                'bio' => '[PLACEHOLDER: one or two sentences about this person]',
                'sort' => $i, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Replace the old starter text (three placeholder members) with an intro; custom text is left alone.
        $page = Page::where('key', 'team')->first();
        if ($page && str_starts_with((string) $page->content, '<p>[CONFIRM: introduce the team.')) {
            $page->update(['content' => '<p>Behind every trip is a small team that plans your route, arranges your stays and answers your questions. We build each journey around you, so you always deal with real people who know Germany.</p><p>[CONFIRM: add the real team story, how long the team has been planning Germany trips, and any accreditation]</p>']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
