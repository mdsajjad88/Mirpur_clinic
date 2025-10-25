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
        Schema::create('product_waitlists', function (Blueprint $table) {
            $table->id();
            $table->string('waitlist_no');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('location_id');
            $table->decimal('quantity_requested', 10, 2);
            $table->string('status')->default('Pending');
            $table->string('sms_status')->default('Not Send');
            $table->string('email_status')->default('Not Send');
            $table->date('estimated_restock_date')->nullable();
            $table->date('restock_date')->nullable();
            $table->date('notification_sent_date')->nullable();
            $table->date('fulfilled_date')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('added_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_waitlists');
    }
};
