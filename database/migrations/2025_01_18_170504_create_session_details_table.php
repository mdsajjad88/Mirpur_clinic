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
    public function up()
    {
        Schema::create('session_details', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade'); // Foreign key referencing transactions table
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade'); // Foreign key referencing contacts table
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Foreign key referencing products table
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('set null'); // Foreign key referencing product_variations table
            $table->integer('session_no')->default('null'); // Session number
            $table->integer('quantity'); // Quantity of the product sold
            $table->decimal('price', 10, 2); // Price per unit
            $table->decimal('subtotal', 10, 2); // Quantity * Price
            $table->timestamps(); // Created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_details');
    }
};
