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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->string('question_media_url')->nullable(); // Path to the question media file
            $table->text('answer');
            $table->string('answer_media_url')->nullable(); // Path to the answer media file
            $table->unsignedTinyInteger('level'); // Assuming levels are from 1-255
            $table->unsignedTinyInteger('diff')->nullable(); // Any additional difficulty measure
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
