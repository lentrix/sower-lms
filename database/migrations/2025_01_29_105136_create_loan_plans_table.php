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
        Schema::create('loan_plans', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->smallInteger('plan_type'); //1-Arawan, 2-Weekly, 3-Bi-Monthly
            $table->bigInteger('category_id')->unsigned();
            $table->float('interest');
            $table->float('penalty');
            $table->string("config")->nullable();
            $table->integer('payment_schedules')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_plans');
    }
};
