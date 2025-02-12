<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no',50);
            $table->bigInteger('loan_plan_id')->unsigned();
            $table->bigInteger('borrower_id')->unsigned();
            $table->string('purpose')->nullable();
            $table->double('amount');
            $table->string('status')->comment("0=request, 1=confirmed, 2=released, 3=completed, 4=denied 5=incomplete");
            $table->double('transaction_fee')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->foreign('loan_plan_id')->references('id')->on('loan_plans');
            $table->foreign('borrower_id')->references('id')->on('borrowers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
