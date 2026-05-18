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
        $admin = User::create([
            'name' => 'Admin Ibu Ida',
            'email' => 'admin@ibuida.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Regular user
        $user2 = User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Categories
        $catMakanan = \App\Models\Category::create(['name' => 'Makanan']);
        $catMinuman = \App\Models\Category::create(['name' => 'Minuman']);
        $catSnack = \App\Models\Category::create(['name' => 'Snack']);

        // Sample Menus
        $menu1 = Menu::create([
            'name' => 'Sayur Asem',
            'description' => 'Sayur asem segar dengan jagung manis',
            'price' => 10000,
            'category' => 'Makanan',
            'category_id' => $catMakanan->id,
            'stock' => 50
        ]);

        $menu2 = Menu::create([
            'name' => 'Air Putih',
            'description' => 'Air mineral dingin',
            'price' => 1000,
            'category' => 'Minuman',
            'category_id' => $catMinuman->id,
            'stock' => 100
        ]);

        $menu3 = Menu::create([
            'name' => 'Nasi Rames Spesial',
            'description' => 'Nasi dengan lauk ayam goreng, telur, tempe, dan sambal',
            'price' => 25000,
            'category' => 'Makanan',
            'category_id' => $catMakanan->id,
            'stock' => 30
        ]);
        
        $menu4 = Menu::create([
            'name' => 'Es Teh Manis',
            'description' => 'Es teh manis segar',
            'price' => 5000,
            'category' => 'Minuman',
            'category_id' => $catMinuman->id,
            'stock' => 80
        ]);
    }
}
