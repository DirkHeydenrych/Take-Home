<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create 50 orders with order items
        Order::factory(50)->create()->each(function ($order) {
            // Create 1-5 order items per order
            $items = OrderItem::factory(rand(1, 5))->make([
                'order_id' => $order->id
            ]);

            $order->orderItems()->saveMany($items);

            // Update order total based on order items
            $order->total = $order->orderItems->sum('total');
            $order->save();
        });
    }
}
