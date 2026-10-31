<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * What every رقيم starts with: خزانة الأدب — verified verses, poets, diwans and sayings (the categories
 * come with their migration). On the public site that is all: every quote and reader there is real.
 * Anywhere else (local, tests) the demo readers and their quotes are added too (DemoSeeder).
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(app()->isProduction() ? LibrarySeeder::class : DemoSeeder::class);
    }
}
