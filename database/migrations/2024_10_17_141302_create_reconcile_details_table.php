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
            Schema::create('reconcile_details', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->unsignedBigInteger('reconcile_id')->nullable(); // Foreign key to reconcile table
                $table->string('name'); // Name field
                $table->string('sku'); // SKU field
                $table->integer('physical_qty'); // Physical quantity
                $table->integer('software_qty'); // Software quantity
                $table->integer('difference'); // Difference in quantity
                $table->decimal('difference_percentage', 5, 2); // Difference percentage
                $table->unsignedBigInteger('created_by')->nullable(); // Created by user ID
                $table->unsignedBigInteger('updated_by')->nullable(); // Updated by user ID
                $table->timestamps();
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade')->name('fk_updated_by_in_reconcile_details_table');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->name('fk_created_by_in_reconcile_details_table');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reconcile_details');
    }
};
