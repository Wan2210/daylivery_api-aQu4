<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500',
            'payment_method' => 'required|string|max:50',
        ]);

        $user = $request->user();

        $cartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Keranjang masih kosong.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $total = 0;

            foreach ($cartItems as $cart) {
                if (!$cart->product) {
                    throw new \Exception('Produk tidak ditemukan.');
                }

                if ($cart->product->stock < $cart->quantity) {
                    throw new \Exception(
                        "Stok {$cart->product->name} tidak mencukupi."
                    );
                }

                $total += $cart->product->price * $cart->quantity;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'payment_method' => $request->payment_method,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->product->price,
                    'subtotal' => $cart->product->price * $cart->quantity,
                ]);

                $cart->product->decrement(
                    'stock',
                    $cart->quantity
                );
            }

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Checkout berhasil.',
                'data' => $order->load('items.product'),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Checkout gagal.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}