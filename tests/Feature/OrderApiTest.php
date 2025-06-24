<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->order = Order::factory()->create([
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => 'Pending',
            'total' => 150.00,
            'is_paid' => false,
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_name' => 'Test Product',
            'quantity' => 2,
            'price' => 75.00,
            'total' => 150.00,
        ]);
    }

    public function test_can_fetch_orders_list()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'customer_name',
                        'customer_email',
                        'status',
                        'total',
                        'is_paid',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);
    }

    public function test_can_filter_orders_by_status()
    {
        // Create orders with different statuses
        Order::factory()->create(['status' => 'Shipped']);
        Order::factory()->create(['status' => 'Cancelled']);

        $response = $this->getJson('/api/orders?status=Pending');

        $response->assertStatus(200);
        $orders = $response->json('data');

        foreach ($orders as $order) {
            $this->assertEquals('Pending', $order['status']);
        }
    }

    public function test_can_filter_orders_by_total_range()
    {
        Order::factory()->create(['total' => 50.00]);
        Order::factory()->create(['total' => 200.00]);

        $response = $this->getJson('/api/orders?min_total=100&max_total=180');

        $response->assertStatus(200);
        $orders = $response->json('data');

        foreach ($orders as $order) {
            $this->assertGreaterThanOrEqual(100, $order['total']);
            $this->assertLessThanOrEqual(180, $order['total']);
        }
    }

    public function test_can_get_order_details()
    {
        $response = $this->getJson("/api/orders/{$this->order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->order->id,
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'status' => 'Pending',
                'total' => '150.00',
                'is_paid' => false,
            ])
            ->assertJsonStructure([
                'order_items' => [
                    '*' => [
                        'id',
                        'product_name',
                        'quantity',
                        'price',
                        'total',
                    ]
                ]
            ]);
    }

    public function test_can_mark_order_as_paid()
    {
        $this->assertFalse($this->order->is_paid);

        $response = $this->patchJson("/api/orders/{$this->order->id}/mark-as-paid");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order marked as paid successfully',
                'order' => [
                    'id' => $this->order->id,
                    'is_paid' => true,
                ]
            ]);

        $this->order->refresh();
        $this->assertTrue($this->order->is_paid);
    }

    public function test_can_update_order_status()
    {
        $response = $this->putJson("/api/orders/{$this->order->id}", [
            'status' => 'Processing'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->order->id,
                'status' => 'Processing',
            ]);

        $this->order->refresh();
        $this->assertEquals('Processing', $this->order->status);
    }

    public function test_can_get_order_statistics()
    {
        // Create additional orders for statistics
        Order::factory()->create(['status' => 'Shipped', 'total' => 100.00, 'is_paid' => true]);
        Order::factory()->create(['status' => 'Cancelled', 'total' => 75.00, 'is_paid' => false]);

        $response = $this->getJson('/api/orders-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_orders',
                'grand_total',
                'average_total',
                'paid_orders',
                'pending_orders',
                'processing_orders',
                'shipped_orders',
                'cancelled_orders',
            ]);

        $stats = $response->json();
        $this->assertGreaterThan(0, $stats['total_orders']);
        $this->assertGreaterThan(0, $stats['grand_total']);
    }

    public function test_validates_order_update_data()
    {
        $response = $this->putJson("/api/orders/{$this->order->id}", [
            'status' => 'InvalidStatus'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_returns_404_for_nonexistent_order()
    {
        $response = $this->getJson('/api/orders/999999');

        $response->assertStatus(404);
    }

    public function test_can_filter_orders_by_date_range()
    {
        $oldOrder = Order::factory()->create([
            'created_at' => now()->subMonths(2)
        ]);

        $recentOrder = Order::factory()->create([
            'created_at' => now()->subDays(5)
        ]);

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->getJson("/api/orders?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $orders = $response->json('data');

        $this->assertGreaterThan(0, count($orders));

        foreach ($orders as $order) {
            $orderDate = $order['created_at'];
            $this->assertGreaterThanOrEqual($startDate, substr($orderDate, 0, 10));
            $this->assertLessThanOrEqual($endDate, substr($orderDate, 0, 10));
        }
    }
}
