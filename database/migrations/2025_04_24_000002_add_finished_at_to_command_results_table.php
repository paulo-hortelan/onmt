<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('command_results') && ! Schema::hasColumn('command_results', 'finished_at')) {
            Schema::table('command_results', function (Blueprint $table) {
                $table->timestamp('finished_at')->nullable()->after('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('command_results', function (Blueprint $table) {
            $table->dropColumn('finished_at');
        });
    }
};
