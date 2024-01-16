<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host')->unique();
            $table->text('username');
            $table->text('password');
            $table->string('brand');
            $table->string('product_model');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('olts');
    }
};
