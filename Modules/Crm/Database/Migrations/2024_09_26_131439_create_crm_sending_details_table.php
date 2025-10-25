<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_sending_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_campaign_id')->unsigned()->nullable(); 
            $table->bigInteger('customer_id')->unsigned()->nullable(); 
            $table->foreign('customer_id')->references('id')->on('contacts')->onDelete('cascade')->name('fk_contacts_id_in_crm_campaign');
            $table->string('customer_name');
            $table->string('mobile');
            $table->bigInteger('send_by')->unsigned()->nullable();
            $table->timestamp('notification_date');
            $table->enum('status', ['pending', 'sent', 'failed', 'successfull']);
            $table->foreign('crm_campaign_id')->references('id')->on('crm_campaigns')->onDelete('cascade')->name('fk_crm_camp_id_in_send_details');
            $table->foreign('send_by')->references('id')->on('users')->onDelete('cascade')->name('fk_user_id_in_crm_send_details');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_sending_details');
    }
};
