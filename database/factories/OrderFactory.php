<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $statuses = ['Pending', 'Processing', 'Shipped', 'Cancelled'];
        $total = $this->faker->randomFloat(2, 10, 1000);

        return [
            'customer_name' => $this->faker->name,
            'customer_email' => $this->faker->unique()->safeEmail,
            'status' => $this->faker->randomElement($statuses),
            'total' => $total,
            'is_paid' => $this->faker->boolean(70), // 70% chance of being paid
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
