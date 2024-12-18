<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommandResultBatchesTable extends Migration
{
    public function up(): void
    {
        Schema::create('command_result_batches', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('description')->nullable();
            $table->string('interface')->nullable();
            $table->string('serial')->nullable();
            $table->string('operator')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('command_result_batches');
    }
}
