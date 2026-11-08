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
        Schema::create('blog_bookmarks', function (Blueprint $table) {
            $table->id();
            // User yang menyimpan artikel
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Artikel yang disimpan
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->timestamps();

            // Satu user hanya bisa menyimpan artikel yang sama satu kali
            $table->unique(['user_id', 'blog_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_bookmarks');
    }
};
