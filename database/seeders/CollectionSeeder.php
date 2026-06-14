<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Collection;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Collection::updateOrCreate(
            ['slug' => 'best-seller'],
            [
                'title' => 'Best Seller',
                'status' => 'active',
            ]
        );
    }
}
