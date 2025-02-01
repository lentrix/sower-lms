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
            $table->bigInteger('loan_type_id')->unsigned();
            $table->float('interest');
            $table->float('penalty');
            $table->string("config")->nullable();
            $table->timestamps();

            $table->foreign('loan_type_id')->references('id')->on('loan_types');
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
