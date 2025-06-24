<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->enum('status', ['Pending', 'Processing', 'Shipped', 'Cancelled'])->default('Pending');
            $table->decimal('total', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            // Add indexes for filtering
            $table->index('status');
            $table->index('created_at');
            $table->index('total');
            $table->index('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
