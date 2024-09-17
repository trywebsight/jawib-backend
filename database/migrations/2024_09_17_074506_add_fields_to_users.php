<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('phone');
            $table->date('bod')->nullable()->after('username');
            $table->string('gender')->nullable()->after('bod');
            $table->string('country')->nullable()->after('gender');
            $table->string('points')->nullable()->after('country');
            // Drop 'credits' column if it exists
            if (Schema::hasColumn('users', 'credits')) {
                $table->dropColumn('credits');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'bod', 'gender', 'country']);

            // Re-add the 'credits' column if needed
            if (!Schema::hasColumn('users', 'credits')) {
                $table->integer('credits')->default(0);
            }
        });
    }
};
