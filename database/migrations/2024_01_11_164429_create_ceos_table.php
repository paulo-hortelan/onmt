<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('interface')->nullable();

            $table->foreignId('dio_id')->constrained(
                table: 'dios',
                indexName: 'ceos_dio_id'
            )->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('ceos');
    }
};
