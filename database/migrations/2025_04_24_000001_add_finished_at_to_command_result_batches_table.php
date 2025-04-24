<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('command_result_batches', function (Blueprint $table) {
            $table->timestamp('finished_at')->nullable()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('command_result_batches', function (Blueprint $table) {
            $table->dropColumn('finished_at');
        });
    }
};
