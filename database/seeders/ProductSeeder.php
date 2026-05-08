<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's products.
     */
    public function run(): void
    {
        Product::upsert([
            [
                'code' => 'R01',
                'name' => 'Red Widget',
                'price' => 32.95,
            ],
            [
                'code' => 'G01',
                'name' => 'Green Widget',
                'price' => 24.95,
            ],
            [
                'code' => 'B01',
                'name' => 'Blue Widget',
                'price' => 7.95,
            ],
        ], ['code'], ['name', 'price']);
    }
}
