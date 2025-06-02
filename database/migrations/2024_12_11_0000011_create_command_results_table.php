<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('command_results')) {
            Schema::create('command_results', function (Blueprint $table) {
                $table->id();
                $table->boolean('success');
                $table->string('command');
                $table->longText('response')->nullable();
                $table->text('error')->nullable();
                $table->json('result')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unsignedBigInteger('batch_id')->nullable()->index();
                $table->foreign('batch_id')->references('id')->on('command_result_batches')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('command_results');
    }
};
