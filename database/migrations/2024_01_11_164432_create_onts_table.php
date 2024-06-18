<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('interface');

            $table->foreignId('cto_id')->constrained(
                table: 'ctos',
                indexName: 'ont_ctos_id'
            )->onUpdate('cascade')->onDelete('cascade');

            $table->integer('port');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('onts');
    }
};
