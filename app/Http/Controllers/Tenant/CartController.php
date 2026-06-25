<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load('items.product');

        return Inertia::render('tenant/ShoppingCartList', [
            'cart' => $cart,
            'cartItems' => $cart->items,
        ]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = max(1, (int) ($validated['quantity'] ?? 1));
        $product = Product::findOrFail($validated['product_id']);

        if (! $product->is_active) {
            return back()->with('error', 'This product is not currently available.');
        }

        if ((int) $product->stock <= 0) {
            return back()->with('error', 'This product is out of stock.');
        }

        $cart = $this->getOrCreateCart($request);

        $cartItem = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $currentQuantity = (int) ($cartItem?->quantity ?? 0);
        $requestedTotal = $currentQuantity + $quantity;

        if ($requestedTotal > (int) $product->stock) {
            return back()->with('error', 'Only ' . $product->stock . ' unit(s) are available for this product.');
        }

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $requestedTotal,
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }

    public function updateItem(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->load('product');
        $product = $cartItem->product;

        if (! $product) {
            $cartItem->delete();

            return back()->with('error', 'This cart item is no longer available.');
        }

        if ((int) $validated['quantity'] > (int) $product->stock) {
            return back()->with('error', 'Only ' . $product->stock . ' unit(s) are available for this product.');
        }

        $cartItem->update([
            'quantity' => (int) $validated['quantity'],
        ]);

        return back()->with('success', 'Cart updated.');
    }

    public function removeItem(CartItem $cartItem)
    {
        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    public function clearCart(Cart $cart)
    {
        $cart->items()->delete();
        $cart->delete();

        return back()->with('success', 'Cart cleared.');
    }

    private function getOrCreateCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate([
                'user_id' => $request->user()->id,
            ], [
                'session_id' => null,
            ]);
        }

        return Cart::firstOrCreate([
            'session_id' => $request->session()->getId(),
        ], [
            'user_id' => null,
        ]);
    }
}
