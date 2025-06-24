<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_name',
        'customer_email',
        'status',
        'total',
        'is_paid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'total' => 'decimal:2',
        'is_paid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order items for the order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        if (!empty($status)) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if (!empty($startDate)) {
            $query->where('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        return $query;
    }

    /**
     * Scope to filter by total range.
     */
    public function scopeByTotalRange($query, $minTotal, $maxTotal)
    {
        if (!empty($minTotal)) {
            $query->where('total', '>=', $minTotal);
        }
        if (!empty($maxTotal)) {
            $query->where('total', '<=', $maxTotal);
        }
        return $query;
    }
}
