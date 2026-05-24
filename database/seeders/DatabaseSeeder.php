<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
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

        $railwayTestUser = User::updateOrCreate(
            ['email' => 'test@kantin.test'],
            [
                'name' => 'User Test Railway',
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

        $menusForOrder = Menu::query()->where('stock', '>', 0)->take(2)->get();

        if ($menusForOrder->isNotEmpty()) {
            $testOrder = Order::updateOrCreate(
                [
                    'user_id' => $railwayTestUser->id,
                    'merchant_order_id' => 'seeded-test-order',
                ],
                [
                    'status' => 'dibuat',
                    'payment_status' => 'paid',
                    'payment_method' => 'SANDBOX',
                    'location' => 'Alamat testing Railway dalam radius 2 KM',
                    'shipping_fee' => 5000,
                    'latitude' => config('canteen.latitude'),
                    'longitude' => config('canteen.longitude'),
                    'distance_km' => 0,
                    'total_price' => 0,
                ]
            );

            $subtotal = 0;

            foreach ($menusForOrder as $index => $menu) {
                $quantity = $index + 1;
                OrderItem::updateOrCreate(
                    [
                        'order_id' => $testOrder->id,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'quantity' => $quantity,
                        'price' => $menu->price,
                    ]
                );

                $subtotal += $menu->price * $quantity;
            }

            $testOrder->total_price = $subtotal + $testOrder->shipping_fee;
            $testOrder->save();
        }
    }
}
