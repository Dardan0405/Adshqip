<?php

namespace Database\Seeders;

use App\Models\SecurityQuestion;
use Illuminate\Database\Seeder;

class SecurityQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            'What is your mother\'s maiden name?',
            'What was the name of your first pet?',
            'What city were you born in?',
            'What is the name of your favorite childhood friend?',
            'What was the make of your first car?',
            'What is your favorite movie?',
            'What was the name of your first school?',
            'What is your favorite book?',
            'What street did you grow up on?',
            'What is your favorite food?',
        ];

        foreach ($questions as $question) {
            SecurityQuestion::updateOrCreate(
                ['question' => $question],
                ['is_active' => true]
            );
        }

        $this->command->info('Seeded ' . count($questions) . ' security questions.');
    }
}
