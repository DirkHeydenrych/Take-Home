<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_has_order_items_relationship()
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $order->orderItems);
        $this->assertEquals(1, $order->orderItems->count());
        $this->assertEquals($orderItem->id, $order->orderItems->first()->id);
    }

    public function test_order_casts_attributes_correctly()
    {
        $order = Order::factory()->create([
            'total' => 123.45,
            'is_paid' => true,
        ]);

        // Laravel casts decimal as string in older versions
        $this->assertIsNumeric($order->total);
        $this->assertIsBool($order->is_paid);
        $this->assertInstanceOf('Carbon\Carbon', $order->created_at);
    }

    public function test_order_scope_by_status()
    {
        Order::factory()->create(['status' => 'Pending']);
        Order::factory()->create(['status' => 'Shipped']);
        Order::factory()->create(['status' => 'Pending']);

        $pendingOrders = Order::byStatus('Pending')->get();
        $shippedOrders = Order::byStatus('Shipped')->get();

        $this->assertEquals(2, $pendingOrders->count());
        $this->assertEquals(1, $shippedOrders->count());

        foreach ($pendingOrders as $order) {
            $this->assertEquals('Pending', $order->status);
        }
    }

    public function test_order_scope_by_date_range()
    {
        $oldOrder = Order::factory()->create(['created_at' => now()->subMonths(2)]);
        $recentOrder = Order::factory()->create(['created_at' => now()->subDays(5)]);

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $filteredOrders = Order::byDateRange($startDate, $endDate)->get();

        $this->assertEquals(1, $filteredOrders->count());
        $this->assertEquals($recentOrder->id, $filteredOrders->first()->id);
    }

    public function test_order_scope_by_total_range()
    {
        Order::factory()->create(['total' => 50.00]);
        Order::factory()->create(['total' => 150.00]);
        Order::factory()->create(['total' => 250.00]);

        $filteredOrders = Order::byTotalRange(100, 200)->get();

        $this->assertEquals(1, $filteredOrders->count());
        $this->assertEquals(150.00, $filteredOrders->first()->total);
    }

    public function test_order_fillable_attributes()
    {
        $order = new Order();
        $fillable = $order->getFillable();

        $expectedFillable = [
            'customer_name',
            'customer_email',
            'status',
            'total',
            'is_paid',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_order_scope_returns_query_when_no_filters()
    {
        Order::factory()->create(['status' => 'Pending']);
        Order::factory()->create(['status' => 'Shipped']);

        // Test that scopes return all records when filters are empty
        $allOrders = Order::byStatus('')->get();
        $this->assertEquals(2, $allOrders->count());

        $allOrdersByDate = Order::byDateRange('', '')->get();
        $this->assertEquals(2, $allOrdersByDate->count());

        $allOrdersByTotal = Order::byTotalRange('', '')->get();
        $this->assertEquals(2, $allOrdersByTotal->count());
    }
}
