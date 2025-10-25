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
        Schema::create('call_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->int('survey_type_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'paused'])->default('draft');
            $table->unsignedInteger('target_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->json('filters')->nullable(); // Store filter criteria as JSON
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('survey_type_id')->references('id')->on('survey_types');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_campaigns');
    }
};
