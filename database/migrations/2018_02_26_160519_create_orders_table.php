<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('year')->nullable();
            $table->string('fuel')->nullable();
            $table->string('vin')->nullable();
            $table->integer('power')->nullable();
            $table->string('description')->nullable();
            $table->string('type');
            $table->string('vehicleNr')->nullable();
            $table->string('image')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('important');
            $table->string('timeLimit')->nullable();
            $table->string('status');
            $table->string('whoAdded');
            $table->string('supplier')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
