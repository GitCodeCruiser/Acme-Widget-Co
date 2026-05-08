<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\DeliveryCharge;
use App\Models\SpendThresholdOffer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private const CART_SESSION_KEY = 'cart';

    public function index() 
    {
        $products = Product::orderBy('name')->get();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function add(AddToCartRequest $request, string $productCode): JsonResponse
    {
        return $this->addProductByCode($request, $productCode, $request->quantity);
    } 

    private function getCart(Request $request): array
    {
        return $request->session()->get(self::CART_SESSION_KEY, []);
    }

    private function addProductByCode(Request $request, string $productCode, int $quantity): JsonResponse
    {
        $product = Product::where('code', $productCode)->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $cart = $this->getCart($request);
        $cartKey = $product->id;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
            if ($cart[$cartKey]['quantity'] <= 0) {
                unset($cart[$cartKey]);
            }
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'product_code' => $product->code,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return response()->json($this->getCart($request), 201);
    }
}