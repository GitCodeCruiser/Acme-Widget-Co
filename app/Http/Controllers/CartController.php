<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\DeliveryCharge;
use App\Models\SpendThresholdOffer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private const CART_SESSION_KEY = 'cart';

    public function add(AddToCartRequest $request, string $productCode): JsonResponse
    {
        return $this->addProductByCode($request, $productCode, $request->quantity);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget(self::CART_SESSION_KEY);

        return response()->json([
            'items' => [],
            'item_count' => 0,
            'subtotal' => 0.0,
            'offer_discount' => 0.0,
            'delivery_charge' => 0.0,
            'total' => 0.0,
        ]);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {

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

        if (isset($cart[$productCode])) {
            $cart[$productCode]['quantity'] += $quantity;
        } else {
            $cart[$productCode] = [
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