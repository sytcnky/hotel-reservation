<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function remove(string $key, Request $request)
    {
        $items = session('cart.items', []);

        if (array_key_exists($key, $items)) {
            unset($items[$key]);
            session(['cart.items' => $items]);
        }

        return redirect()->back()->with('ok', 'cart_item_removed');
    }
}
