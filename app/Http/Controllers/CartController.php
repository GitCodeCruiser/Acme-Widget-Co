<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
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

    public function total(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $subtotal = $this->calculateSubtotal($cart);
        $offerDiscount = $this->calculateOfferDiscount($cart);
        $discountedSubtotal = $subtotal - $offerDiscount;

        $deliveryCharge = DeliveryCharge::where('min_threshold', '<=', $discountedSubtotal)
            ->where('max_threshold', '>=', $discountedSubtotal)
            ->value('charge') ?? 0.0;

        return response()->json([
            'items' => $cart,
            'subtotal' => round($subtotal, 2),
            'offer_discount' => round($offerDiscount, 2),
            'delivery_charge' => round($deliveryCharge, 2),
            'total' => round($discountedSubtotal + $deliveryCharge, 2),
        ]);
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
            if ($quantity <= 0) {
                return response()->json($this->getCart($request), 200);
            }

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

    private function calculateSubtotal(array $cart): float
    {
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'];

            $subtotal += $price * $quantity;
        }

        return $subtotal;
    }

    private function calculateOfferDiscount(array $cart): float
    {
        if (empty($cart)) {
            return 0.0;
        }

        $productIds = array_filter(array_column($cart, 'product_id'));
        $allOffers = SpendThresholdOffer::whereIn('product_id', $productIds)->get()->groupBy('product_id');

        $totalDiscount = 0.0;

        foreach ($cart as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = $item['quantity'];
            $unitPrice = $item['price'];
            $lineSubtotal = $unitPrice * $quantity;
            $lineDiscount = 0.0;

            if (! $productId) {
                continue;
            }

            $offers = $allOffers[$productId] ?? collect();

            foreach ($offers as $offer) {
                $everyNth = $offer->every_nth;
                $eligibleCount = (int) ($quantity/$everyNth);

                if ($eligibleCount <= 0) {
                    continue;
                }

                if ($offer->discount_type == 1) {
                    $discountPerItem = $unitPrice * ($offer->discount_amount / 100);
                } else {
                    $discountPerItem = $offer->discount_amount;
                }

                $lineDiscount += $eligibleCount * $discountPerItem;
            }

            $totalDiscount += min($lineDiscount, $lineSubtotal);
        }

        return $totalDiscount;
    }
}