<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Categories;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create categories
        categories::factory(6)->create();
    }
}
