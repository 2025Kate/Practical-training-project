<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Таблица users
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('username')->unique();
                $table->string('name');
                $table->boolean('privacy')->default(false);
                $table->timestamps();
                
                $table->index('username');
            });
        }

        // Таблица posts
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
                $table->string('content', 280);
                $table->datetime('DateTime');
                $table->timestamps();
                
                $table->index(['author_id', 'DateTime']);
            });
        }

        // Таблица subscribe
        if (!Schema::hasTable('subscribe')) {
            Schema::create('subscribe', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('subscriber_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['user_id', 'subscriber_id']);
                $table->index('subscriber_id');
            });
        }

        // Таблица post_mention
        if (!Schema::hasTable('post_mention')) {
            Schema::create('post_mention', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
                $table->foreignId('mentioned_user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
                
                $table->index('mentioned_user_id');
            });
        }

        // Таблица hashtags
        if (!Schema::hasTable('hashtags')) {
            Schema::create('hashtags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // Таблица post_hashtag
        if (!Schema::hasTable('post_hashtag')) {
            Schema::create('post_hashtag', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
                $table->foreignId('hashtag_id')->constrained('hashtags')->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['post_id', 'hashtag_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('post_hashtag');
        Schema::dropIfExists('hashtags');
        Schema::dropIfExists('post_mention');
        Schema::dropIfExists('subscribe');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
    }
};