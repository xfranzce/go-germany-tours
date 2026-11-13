<?php

namespace Database\Seeders;

use App\Models\QuizOption;
use App\Models\QuizStep;
use Illuminate\Database\Seeder;

/** Hero quiz steps (the final contact step is built into the form, not stored). */
class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            ['destination', 'Would you like to visit Quedlinburg or Cochem?', 'tiles', [['Quedlinburg', '🏘️'], ['Cochem', '🏰']]],
            ['time_of_year', 'What time of year do you want to travel?', 'tiles', [['Spring', '🌸'], ['Summer', '☀️'], ['Autumn', '🍂'], ['Winter', '❄️']]],
            ['travel_style', 'What is your favourite way to see a new country?', 'tiles', [
                ['By coach with other travellers', '🚌'], ['Driving yourself', '🚗'], ['With a tour guide driving', '🧭'], ["Don't know", '❓']]],
            ['group_size', 'How many people are in your group?', 'dropdown', array_map(fn ($n) => [(string) $n, null], [1, 2, 3, 4, 5, 6, 7, 8, '9 or more'])],
            ['nights', 'How many nights?', 'dropdown', array_map(fn ($n) => [$n, null], ['3 to 4', '5 to 6', '7 to 8', '9 to 10', '11 to 14', '15 or more'])],
            ['flights_booked', 'Have you booked flights?', 'tiles', [['Yes', '✈️'], ['No', '🛫']]],
            ['budget', 'Have you a budget in mind for your trip?', 'tiles', [['$1K per person', '💵'], ['$2K per person', '💵'], ['$3K per person', '💰'], ['$4K+ per person', '💰']]],
            ['likelihood', 'How likely are you to come?', 'tiles', [
                ["I'm 100% sure I'm going", '✅'], ["I'm 80% sure", '👍'], ['50/50', '🤔'], ["Unsure, just researching", '🔎']]],
        ];

        foreach ($steps as $i => [$key, $question, $type, $options]) {
            $step = QuizStep::updateOrCreate(['key' => $key], ['question' => $question, 'type' => $type, 'sort' => $i, 'is_active' => true]);
            if ($step->options()->exists()) {
                continue;
            }
            foreach ($options as $j => [$label, $icon]) {
                QuizOption::create(['quiz_step_id' => $step->id, 'label' => (string) $label, 'icon' => $icon, 'sort' => $j]);
            }
        }
    }
}
