<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\QuizOption;
use App\Models\QuizStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_step_keeps_option_ids_and_active_flags_and_supports_add_remove_reorder(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery']);
        $step = QuizStep::where('key', 'time_of_year')->first();
        $opts = $step->options;
        $idsBefore = $opts->pluck('id')->all();

        // Post back exactly what the form sends: first option off, others on, one renamed, one removed, one added.
        $rows = [];
        foreach ($opts->take(3) as $i => $o) {
            $row = ['id' => $o->id, 'label' => $o->label, 'icon' => $o->icon, 'sort' => $i];
            if ($i !== 0) {
                $row['is_active'] = '1';
            }
            $rows[] = $row;
        }
        $rows[1]['label'] = 'Summer holidays';
        $rows[] = ['id' => '', 'label' => 'Indian summer', 'icon' => '??', 'sort' => 9, 'is_active' => '1'];

        $this->actingAs($admin, 'admin')->put("/admin/quiz/{$step->id}", [
            'question' => 'When?', 'type' => 'tiles', 'sort' => 1, 'is_active' => '1', 'options' => $rows,
        ])->assertSessionHas('status');

        $fresh = $step->fresh()->options;
        $this->assertSame(array_slice($idsBefore, 0, 3), $fresh->take(3)->pluck('id')->all(), 'existing options keep their ids');
        $this->assertFalse((bool) $fresh[0]->is_active);
        $this->assertTrue((bool) $fresh[1]->is_active);
        $this->assertSame('Summer holidays', $fresh[1]->label);
        $this->assertCount(4, $fresh);                                // 3 kept + 1 added, 4th original removed
        $this->assertNotContains($opts[3]->id, $fresh->pluck('id')->all());
        $this->assertSame('Indian summer', $fresh->last()->label);

        // The public quiz only offers active options
        $labels = collect(QuizStep::forQuiz())->firstWhere('key', 'time_of_year')['options'];
        $this->assertNotContains($opts[0]->label, array_column($labels, 'label'));
    }
}
