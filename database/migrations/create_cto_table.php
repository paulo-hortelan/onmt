<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['1x8', '1x16']);

            $table->foreignId('ceo_splitter_id')->constrained(
                table: 'ceo_splitters',
                indexName: 'cto_ceo_splitters_id'
            )->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('ctos');
    }
};
