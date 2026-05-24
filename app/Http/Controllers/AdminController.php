<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::whereIn('status', ['dibuat', 'diantar', 'sampai', 'selesai'])->sum('total_price');
        $totalUsers = User::where('is_admin', false)->count();
        $orders = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();
        return view('admin.dashboard', compact('totalOrders', 'totalRevenue', 'totalUsers', 'orders'));
    }

    public function menus()
    {
        $menus = Menu::all();
        return view('admin.menus', compact('menus'));
    }

    public function storeMenu(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/menus');
            $data['image_url'] = str_replace('public/', 'storage/', $path);
        }

        Menu::create($data);
        return back()->with('success', 'Menu added');
    }

    public function editMenu($id)
    {
        $menu = Menu::findOrFail($id);
        return view('admin.menus_edit', compact('menu'));
    }

    public function updateMenu(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/menus');
            $data['image_url'] = str_replace('public/', 'storage/', $path);
        }

        $menu->update($data);
        return redirect()->route('admin.menus')->with('success', 'Menu updated successfully');
    }

    public function deleteMenu($id)
    {
        Menu::destroy($id);
        return back()->with('success', 'Menu deleted');
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_admin = $request->has('is_admin');
        $user->save();
        return back()->with('success', 'User updated');
    }

    public function deleteUser($id)
    {
        User::destroy($id);
        return back()->with('success', 'User deleted');
    }

    public function orders()
    {
        $orders = Order::with('user', 'items.menu')->orderBy('created_at', 'desc')->get();
        
        // Auto-complete orders that are "sampai" and > 24 hours old
        foreach ($orders as $order) {
            if ($order->status == 'sampai') {
                $hoursDiff = \Carbon\Carbon::now()->diffInHours($order->updated_at);
                if ($hoursDiff >= 24) {
                    $order->status = 'selesai';
                    $order->save();
                }
            }
        }
        
        return view('admin.orders', compact('orders'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();
        return back()->with('success', 'Order status updated');
    }

    public function kasir()
    {
        $menus = Menu::where('stock', '>', 0)->get();
        return view('admin.kasir', compact('menus'));
    }

    public function kasirCheckout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paymentMethod' => 'required|string',
            'customerName' => 'required|string',
        ]);

        $order = Order::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'status' => 'pending',
            'total_price' => 0,
            'location' => 'Kasir - Pelanggan: ' . $request->customerName,
        ]);

        $totalPrice = 0;
        foreach ($request->items as $item) {
            $menu = Menu::find($item['id']);
            if ($menu->stock < $item['quantity']) {
                return response()->json(['success' => false, 'message' => 'Stok ' . $menu->name . ' tidak mencukupi (Sisa: ' . $menu->stock . ')']);
            }
            // Kurangi stok
            $menu->stock -= $item['quantity'];
            $menu->save();

            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $menu->id,
                'quantity' => $item['quantity'],
                'price' => $menu->price
            ]);
            $totalPrice += ($menu->price * $item['quantity']);
        }

        $order->total_price = $totalPrice;
        $order->save();

        // Re-fetch to ensure fresh data
        $order = Order::with('items.menu')->find($order->id);

        // Duitku API
        $merchantCode = config('duitku.merchant_code');
        $apiKey = config('duitku.api_key');
        $paymentAmount = (int)$order->total_price;
        $merchantOrderId = $order->id . '-POS-' . time();
        $productDetails = "Pembayaran Pesanan POS #" . $order->id;
        $email = \Illuminate\Support\Facades\Auth::user()->email;
        $customerVaName = $request->customerName;
        $callbackUrl = route('payment.callback');
        $returnUrl = route('admin.kasir'); // Kembali ke kasir setelah bayar
        $expiryPeriod = 60; 

        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        $itemDetails = [];
        foreach ($order->items as $orderItem) {
            $itemDetails[] = [
                'name' => $orderItem->menu ? $orderItem->menu->name : 'Menu',
                'price' => (int)$orderItem->price,
                'quantity' => (int)$orderItem->quantity
            ];
        }

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => (int)$paymentAmount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => '',
            'merchantUserInfo' => '',
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => '',
            // 'itemDetails' => $itemDetails, // Di-comment agar tidak error mismatch harga di Duitku
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod,
            'paymentMethod' => $request->paymentMethod
        ];

        $url = config('duitku.is_production') 
            ? 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry' 
            : 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';

        try {
            $response = \Illuminate\Support\Facades\Http::post($url, $params);
            $data = $response->json();

            if (isset($data['paymentUrl'])) {
                return response()->json([
                    'success' => true,
                    'payment_url' => $data['paymentUrl']
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => $data['Message'] ?? $data['message'] ?? 'Gagal memproses Duitku',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
