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
            $table->string('interface')->nullable();
            $table->string('serial')->nullable();
            $table->json('commands')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('command_result_batches');
    }
}
