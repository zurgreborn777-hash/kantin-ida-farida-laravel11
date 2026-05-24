<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function menu()
    {
        $menus = Menu::all();
        return view('menu', compact('menus'));
    }

    public function profile()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->with('items.menu')->orderBy('created_at', 'desc')->get();
        return view('profile', compact('user', 'orders'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user->name = $request->name;
        $user->phone = $request->phone;
        if ($request->password) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $user->save();
        
        return back()->with('success', 'Profile updated successfully!');
    }

    public function order(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $menu = Menu::find($request->menu_id);
        
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where(function($q) {
                $q->whereNull('location')->orWhere('location', 'not like', 'Kasir - %');
            })->first();
        if (!$order) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'total_price' => 0,
            ]);
        }

        $existingItem = OrderItem::where('order_id', $order->id)->where('menu_id', $menu->id)->first();
        $currentQty = $existingItem ? $existingItem->quantity : 0;

        if ($currentQty + $request->quantity > $menu->stock) {
            $sisaBisaDitambah = max(0, $menu->stock - $currentQty);
            $pesanError = $currentQty > 0 
                ? 'Stok tidak mencukupi. Di keranjang sudah ada ' . $currentQty . ' porsi. Sisa yang bisa ditambahkan: ' . $sisaBisaDitambah
                : 'Stok tidak mencukupi. Maksimal: ' . $menu->stock;

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $pesanError]);
            }
            return redirect()->back()->withErrors(['quantity' => $pesanError]);
        }

        if ($existingItem) {
            $existingItem->quantity += $request->quantity;
            $existingItem->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $menu->id,
                'quantity' => $request->quantity,
                'price' => $menu->price
            ]);
        }

        $this->recalculateOrder($order);

        if (request()->wantsJson()) {
            $cartCount = $order->items()->sum('quantity');
            return response()->json(['success' => true, 'cartCount' => $cartCount]);
        }

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang!');
    }

    public function cart()
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where(function($q) {
                $q->whereNull('location')->orWhere('location', 'not like', 'Kasir - %');
            })
            ->with('items.menu')->first();
        return view('cart', compact('order'));
    }

    public function updateCartItem(Request $request, $id)
    {
        $request->validate(['action' => 'required|in:increase,decrease']);
        $item = OrderItem::where('id', $id)->whereHas('order', function($q) {
            $q->where('user_id', Auth::id())
              ->where('status', 'pending')
              ->where(function($q2) {
                  $q2->whereNull('location')->orWhere('location', 'not like', 'Kasir - %');
              });
        })->firstOrFail();

        if ($request->action == 'increase') {
            if ($item->quantity + 1 > $item->menu->stock) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Stok maksimal: ' . $item->menu->stock]);
                }
                return redirect()->back()->withErrors(['quantity' => 'Stok maksimal tercapai']);
            }
            $item->quantity += 1;
            $item->save();
        } else {
            if ($item->quantity > 1) {
                $item->quantity -= 1;
                $item->save();
            } else {
                $item->delete();
            }
        }

        $this->recalculateOrder($item->order);

        if (request()->wantsJson()) {
            $order = Order::where('id', $item->order_id)->with('items.menu')->first();
            $cartCount = $order->items()->sum('quantity');
            return response()->json([
                'success' => true, 
                'cartCount' => $cartCount,
                'totalPrice' => number_format($order->total_price, 0, ',', '.'),
                'itemQuantity' => $item->exists ? $item->quantity : 0,
                'itemId' => $id
            ]);
        }

        return redirect()->back();
    }

    public function removeCartItem($id)
    {
        $item = OrderItem::where('id', $id)->whereHas('order', function($q) {
            $q->where('user_id', Auth::id())
              ->where('status', 'pending')
              ->where(function($q2) {
                  $q2->whereNull('location')->orWhere('location', 'not like', 'Kasir - %');
              });
        })->firstOrFail();
        
        $order = $item->order;
        $item->delete();
        
        $this->recalculateOrder($order);

        if (request()->wantsJson()) {
            $cartCount = $order->items()->sum('quantity');
            return response()->json([
                'success' => true, 
                'cartCount' => $cartCount,
                'totalPrice' => number_format($order->total_price, 0, ',', '.')
            ]);
        }

        return redirect()->back()->with('success', 'Item dihapus dari keranjang.');
    }

    private function recalculateOrder($order)
    {
        $total = 0;
        foreach ($order->items as $item) {
            $total += ($item->price * $item->quantity);
        }
        $order->total_price = $total;
        $order->save();
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'location' => 'required|string|max:1000',
            'ongkir' => 'nullable|integer',
            'distance' => 'nullable|numeric',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where(function($q) {
                $q->whereNull('location')->orWhere('location', 'not like', 'Kasir - %');
            })
            ->with('items.menu')->first();
        
        if (!$order || $order->items->count() == 0) {
            return request()->wantsJson() 
                ? response()->json(['success' => false, 'message' => 'Keranjang kosong!']) 
                : redirect()->back()->withErrors(['cart' => 'Keranjang kosong!']);
        }

        $basePrice = 0;
        foreach ($order->items as $item) {
            $basePrice += ($item->price * $item->quantity);
        }

        $ongkir = (int)$request->input('ongkir', 0);

        $order->location = $request->location;
        $order->shipping_fee = $ongkir;
        $order->latitude = $request->lat;
        $order->longitude = $request->lng;
        $order->distance_km = $request->distance;
        $order->total_price = $basePrice + $ongkir;
        $order->save();

        // Re-fetch to ensure fresh items and relations
        $order = Order::with('items.menu')->find($order->id);

        $merchantCode = config('duitku.merchant_code');
        $apiKey = config('duitku.api_key');
        $paymentAmount = (int)$order->total_price;
        $merchantOrderId = $order->id . '-' . time();
        $productDetails = "Pembayaran Pesanan #" . $order->id;
        $email = Auth::user()->email ?? 'customer@example.com';
        $customerVaName = Auth::user()->name;
        $callbackUrl = 'https://nasi-rames-v2-production.up.railway.app/callback';
        $returnUrl = route('home');
        $expiryPeriod = 60; // 60 minutes

        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'name' => $item->menu ? $item->menu->name : 'Menu',
                'price' => (int)$item->price,
                'quantity' => (int)$item->quantity
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
            'phoneNumber' => Auth::user()->phone ?? '',
            // 'itemDetails' => $itemDetails, // Di-comment agar tidak error mismatch harga di Duitku
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod,
            'paymentMethod' => $request->paymentMethod ?? 'SP' // QRIS as default
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
                    'message' => $data['Message'] ?? $data['message'] ?? 'Gagal membuat transaksi Duitku',
                    'debug_data' => $data,
                    'status_code' => $response->status()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function callback(Request $request)
    {
        $apiKey = config('duitku.api_key');
        $merchantCode = $request->merchantCode;
        $amount = $request->amount;
        $merchantOrderId = $request->merchantOrderId;
        $productDetail = $request->productDetail;
        $additionalParam = $request->additionalParam;
        $paymentCode = $request->paymentCode;
        $resultCode = $request->resultCode;
        $signature = $request->signature;

        $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);

        if ($signature == $calcSignature) {
            if ($resultCode == '00') {
                // Payment success
                $orderId = explode('-', $merchantOrderId)[0];
                $order = Order::find($orderId);
                if ($order && $order->status == 'pending') {
                    $order->status = 'dibuat';
                    $order->save();

                    // Update stok menu
                    foreach ($order->items as $item) {
                        if ($item->menu) {
                            $item->menu->decrement('stock', $item->quantity);
                        }
                    }
                }
            }
            return response('OK', 200);
        } else {
            return response('Invalid signature', 400);
        }
    }

    public function paymentSuccess(Request $request)
    {
        return redirect(route('home'))->with('success', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->where('status', '!=', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Check for orders that are "sampai" and > 24 hours old, auto-complete them
        foreach ($orders as $order) {
            if ($order->status == 'sampai') {
                $hoursDiff = \Carbon\Carbon::now()->diffInHours($order->updated_at);
                if ($hoursDiff >= 24) {
                    $order->status = 'selesai';
                    $order->save();
                }
            }
        }
            
        return view('orders', compact('orders'));
    }

    public function confirmOrder(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        if ($order->status == 'sampai') {
            $order->status = 'selesai';
            $order->save();
            return redirect()->back()->with('success', 'Pesanan telah dikonfirmasi selesai. Terima kasih!');
        }
        return redirect()->back()->withErrors(['error' => 'Pesanan tidak valid untuk dikonfirmasi.']);
    }
}
