<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\Product;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'total_amount' => 0, // se calculará después
            'status' => $this->faker->randomElement([
                'pending',
                'approved',
                'rejected'
            ]),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Quote $quote) {

            // Tomar entre 1 y 5 productos existentes
            $products = Product::inRandomOrder()
                ->take(rand(1, 5))
                ->get();

            $total = 0;
            $order = 1;

            foreach ($products as $product) {

                $quantity = rand(1, 3);
                $price = $product->price;

                $quote->products()->attach($product->id, [
                    'price' => $price,
                    'quantity' => $quantity,
                    'order' => $order++,
                ]);

                $total += $price * $quantity;
            }

            // actualizar total
            $quote->update([
                'total_amount' => $total
            ]);
        });
    }
}