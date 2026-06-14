<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        SocialMedia::firstOrCreate();

        Contact::firstOrCreate([], [
            'company_name' => 'Hobby Indonesia',
            'telephone' => '+6282122538796',
            'email' => 'hobbyindonesia@gmail.com',
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            CollectionSeeder::class,
        ]);
    }
}
