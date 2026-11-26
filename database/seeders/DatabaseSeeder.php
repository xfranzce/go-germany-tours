<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->call([
            PagesSeeder::class,
            CatalogSeeder::class,
            QuizSeeder::class,
            ContentSeeder::class,
            TourTypeFaqSeeder::class,
            ImageSeeder::class,
            TravelStyleSeeder::class,
            FiveToursPerDestinationSeeder::class,
            MoreBlogPostsSeeder::class,
        ]);
    }

    /** Admin account comes from .env only. Nothing is hard-coded. */
    private function seedAdmin(): void
    {
        $email = env('ADMIN_SEED_EMAIL');
        $password = env('ADMIN_SEED_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_SEED_EMAIL / ADMIN_SEED_PASSWORD not set in .env: no admin user created.');

            return;
        }

        AdminUser::firstOrCreate(
            ['email' => $email],
            ['name' => env('ADMIN_SEED_NAME', 'Site Admin'), 'password' => $password],
        );
    }
}
