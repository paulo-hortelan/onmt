<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceo_splitters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['1x8', '1x16']);
            $table->integer('slot');
            $table->integer('pon');

            $table->foreignId('ceo_id')->constrained(
                table: 'ceos',
                indexName: 'ceo_splitters_ceo_id'
            )->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('ceo_splitters');
    }
};
