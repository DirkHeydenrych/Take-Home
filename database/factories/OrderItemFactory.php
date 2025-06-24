<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $products = [
            'Laptop Computer',
            'Wireless Mouse',
            'Keyboard',
            'Monitor',
            'Smartphone',
            'Tablet',
            'Headphones',
            'Webcam',
            'Printer',
            'External Hard Drive',
            'USB Cable',
            'Charger',
            'Speaker',
            'Microphone',
            'Router'
        ];

        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 5, 500);

        return [
            'product_name' => $this->faker->randomElement($products),
            'product_description' => $this->faker->sentence(6),
            'quantity' => $quantity,
            'price' => $price,
            'total' => $quantity * $price,
        ];
    }
}
