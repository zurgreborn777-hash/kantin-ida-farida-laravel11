<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Menu;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@ibuida.com'],
            [
                'name' => 'Admin Ibu Ida',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // Regular user
        User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        // Categories
        $catMakanan = \App\Models\Category::updateOrCreate(['name' => 'Makanan']);
        $catMinuman = \App\Models\Category::updateOrCreate(['name' => 'Minuman']);
        \App\Models\Category::updateOrCreate(['name' => 'Snack']);

        // Sample Menus
        Menu::updateOrCreate(
            ['name' => 'Sayur Asem'],
            [
                'description' => 'Sayur asem segar dengan jagung manis',
                'price' => 10000,
                'category' => 'Makanan',
                'category_id' => $catMakanan->id,
                'stock' => 50,
            ]
        );

        Menu::updateOrCreate(
            ['name' => 'Air Putih'],
            [
                'description' => 'Air mineral dingin',
                'price' => 1000,
                'category' => 'Minuman',
                'category_id' => $catMinuman->id,
                'stock' => 100,
            ]
        );

        Menu::updateOrCreate(
            ['name' => 'Nasi Rames Spesial'],
            [
                'description' => 'Nasi dengan lauk ayam goreng, telur, tempe, dan sambal',
                'price' => 25000,
                'category' => 'Makanan',
                'category_id' => $catMakanan->id,
                'stock' => 30,
            ]
        );
        
        Menu::updateOrCreate(
            ['name' => 'Es Teh Manis'],
            [
                'description' => 'Es teh manis segar',
                'price' => 5000,
                'category' => 'Minuman',
                'category_id' => $catMinuman->id,
                'stock' => 80,
            ]
        );
    }
}
