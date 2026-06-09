<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::get('/menu/{menu}', [HomeController::class, 'menuShow'])->name('menu.show');

Route::get('/hello', function() {
    return "Hello! If you see this, the new code is deployed.";
});

// Temporary route to seed database
Route::get('/seed-db', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return "Database seeded successfully!<br><br><b>Admin:</b> admin@ibuida.com / password<br><b>User:</b> user@test.com / password";
    } catch (\Exception $e) {
        return "Error seeding database: " . $e->getMessage();
    }
});
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    Route::post('/profile', [HomeController::class, 'updateProfile'])->name('profile.update');
    
    Route::get('/cart', [HomeController::class, 'cart'])->name('cart');
    Route::post('/order', [HomeController::class, 'order'])->name('order.add');
    Route::post('/cart/delivery-preview', [HomeController::class, 'deliveryPreview'])->name('cart.delivery-preview');
    Route::post('/cart/update/{id}', [HomeController::class, 'updateCartItem'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [HomeController::class, 'removeCartItem'])->name('cart.remove');
    Route::post('/checkout', [HomeController::class, 'checkout'])->name('order.checkout');
    Route::post('/payment/success', [HomeController::class, 'paymentSuccess'])->name('payment.success');
    
    Route::get('/orders', [HomeController::class, 'myOrders'])->name('orders.my');
    Route::post('/orders/{id}/confirm', [HomeController::class, 'confirmOrder'])->name('orders.confirm');
    Route::get('/invoice/{order}', [HomeController::class, 'invoice'])->name('invoice.show');
});

Route::post('/callback', [HomeController::class, 'callback'])->name('payment.callback');

Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/menus', [AdminController::class, 'menus'])->name('admin.menus');
    Route::post('/menus', [AdminController::class, 'storeMenu'])->name('admin.menus.store');
    Route::get('/menus/{id}/edit', [AdminController::class, 'editMenu'])->name('admin.menus.edit');
    Route::post('/menus/{id}', [AdminController::class, 'updateMenu'])->name('admin.menus.update');
    Route::delete('/menus/{id}', [AdminController::class, 'deleteMenu'])->name('admin.menus.delete');
    
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.status');
    
    Route::get('/kasir', [AdminController::class, 'kasir'])->name('admin.kasir');
    Route::post('/kasir/checkout', [AdminController::class, 'kasirCheckout'])->name('admin.kasir.checkout');

});
