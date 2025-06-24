<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource with filtering capabilities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Order::with('orderItems');

        // Apply filters
        if ($request->has('status') && !empty($request->status)) {
            $query->byStatus($request->status);
        }

        if ($request->has('start_date') || $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        if ($request->has('min_total') || $request->has('max_total')) {
            $query->byTotalRange($request->min_total, $request->max_total);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Get statistics for the filtered orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $query = Order::query();

        // Apply the same filters as index
        if ($request->has('status') && !empty($request->status)) {
            $query->byStatus($request->status);
        }

        if ($request->has('start_date') || $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        if ($request->has('min_total') || $request->has('max_total')) {
            $query->byTotalRange($request->min_total, $request->max_total);
        }

        $stats = $query->select([
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total) as grand_total'),
            DB::raw('AVG(total) as average_total'),
            DB::raw('COUNT(CASE WHEN is_paid = 1 THEN 1 END) as paid_orders'),
            DB::raw('COUNT(CASE WHEN status = "Pending" THEN 1 END) as pending_orders'),
            DB::raw('COUNT(CASE WHEN status = "Processing" THEN 1 END) as processing_orders'),
            DB::raw('COUNT(CASE WHEN status = "Shipped" THEN 1 END) as shipped_orders'),
            DB::raw('COUNT(CASE WHEN status = "Cancelled" THEN 1 END) as cancelled_orders'),
        ])->first();

        return response()->json($stats);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        $order->load('orderItems');
        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'sometimes|in:Pending,Processing,Shipped,Cancelled',
            'is_paid' => 'sometimes|boolean',
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
        ]);

        $order->update($request->only(['status', 'is_paid', 'customer_name', 'customer_email']));
        $order->load('orderItems');

        return response()->json($order);
    }

    /**
     * Mark an order as paid.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsPaid(Order $order)
    {
        $order->update(['is_paid' => true]);
        $order->load('orderItems');

        return response()->json([
            'message' => 'Order marked as paid successfully',
            'order' => $order
        ]);
    }
}
