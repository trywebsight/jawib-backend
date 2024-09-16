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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedInteger('max_uses_per_user')->default(1);
            $table->unsignedInteger('max_users')->nullable(); // Null means unlimited
            $table->unsignedInteger('total_uses')->default(0); // Tracks total uses
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('uses')->default(0);
            $table->timestamps();

            $table->unique(['coupon_id', 'user_id']); // Ensure a user can have only one entry per coupon
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('coupon_user');
    }
};
