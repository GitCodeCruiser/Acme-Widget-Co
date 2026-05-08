<?php

namespace Database\Seeders;

use App\Models\DeliveryCharge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryChargeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed delivery charges by subtotal threshold.
     */
    public function run(): void
    {
        DeliveryCharge::upsert([
            [
                'min_threshold' => 0.00,
                'max_threshold' => 49.99,
                'charge' => 4.95,
            ],
            [
                'min_threshold' => 50.00,
                'max_threshold' => 89.99,
                'charge' => 2.95,
            ],
            [
                'min_threshold' => 90.00,
                'max_threshold' => 999999.99,
                'charge' => 0.00,
            ],
        ], ['min_threshold', 'max_threshold'], ['charge']);
    }
}
