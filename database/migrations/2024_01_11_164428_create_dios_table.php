<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['6', '12', '24', '36', '48', '64', '72', '96', '144']);

            $table->foreignId('olt_id')->constrained(
                table: 'olts',
                indexName: 'dios_olt_id'
            )->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('dios');
    }
};
