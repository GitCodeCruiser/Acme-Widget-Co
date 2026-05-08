<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Product;
use App\Models\SpendThresholdOffer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpendThresholdOfferSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $product = Product::where('code', 'R01')->firstOrFail();

        $offer = Offer::firstOrCreate(
            ['name' => 'Buy one red widget, get the second half price'],
            ['description' => 'For every two red widgets, the second widget is 50% off.']
        );

        SpendThresholdOffer::updateOrCreate(
            [
                'offer_id' => $offer->id,
                'product_id' => $product->id,
            ],
            [
                'every_nth' => 2,
                'discount_type' => 1,
                'discount_amount' => 50,
            ]
        );
    }
}