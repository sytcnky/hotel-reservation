<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Sepetten tek bir ürünü kaldırır.
     *
     * - Eğer verilen key mevcutsa ilgili item silinir.
     * - Silme sonrası sepet tamamen boş kalırsa "cart" session anahtarı da temizlenir.
     */
    public function remove(string $key, Request $request)
    {
        $items = session('cart.items', []);

        if (array_key_exists($key, $items)) {
            unset($items[$key]);

            if (empty($items)) {
                // Son ürün de silindiyse sepeti tamamen sıfırla
                session()->forget('cart');
            } else {
                session(['cart.items' => $items]);
            }
        }

        return redirect()
            ->back()
            ->with('ok', 'cart_item_removed');
    }
}
