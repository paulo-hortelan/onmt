<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host_connection');
            $table->string('host_server')->unique();
            $table->text('username');
            $table->text('password');
            $table->string('brand');
            $table->string('model');
            $table->timestamps();
        });

        Schema::create('dios', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->foreignId('olt_id')->constrained(
                table: 'olts',
                indexName: 'dios_olt_id'
            )->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

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

        Schema::create('onts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('interface');

            $table->foreignId('cto_id')->constrained(
                table: 'ctos',
                indexName: 'ont_ctos_id'
            )->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
        Schema::dropIfExists('dios');
        Schema::dropIfExists('ceos');
        Schema::dropIfExists('ceo_splitters');
        Schema::dropIfExists('ctos');
        Schema::dropIfExists('onts');
    }
};
