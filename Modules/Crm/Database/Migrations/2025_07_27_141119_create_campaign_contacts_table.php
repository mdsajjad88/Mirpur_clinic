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
        Schema::create('campaign_contacts', function (Blueprint $table) {
            $table->id();
            $table->int('campaign_id');
            $table->int('contact_id');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->int('assigned_to')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->text('notes')->nullable();
            $table->int('feedback_form_id')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('call_campaigns');
            $table->foreign('contact_id')->references('id')->on('contacts');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('feedback_form_id')->references('id')->on('feedback_form_call_centers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_contacts');
    }
};
