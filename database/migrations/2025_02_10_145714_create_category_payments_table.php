<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('category_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sell_id')->nullable(); // Nullable for sell return payments
            $table->unsignedBigInteger('return_id')->nullable(); // Nullable for sell payments
            $table->unsignedBigInteger('category_id'); // Link to category table
            $table->unsignedBigInteger('location_id');
            $table->string('payment_method'); // Cash, Card, Bank, etc.
            $table->decimal('amount', 15, 2);
            $table->decimal('line_discount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->enum('type', ['sell', 'sell_return']); // Sell or Sell Return Payment
            $table->timestamps();

            // Foreign Keys
            $table->foreign('sell_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('return_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_payments');
    }
};
