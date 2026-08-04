<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gym_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedTinyInteger('rating');           // 1-5
            $t->string('title')->nullable();
            $t->text('comment')->nullable();
            $t->unsignedInteger('likes_count')->default(0);
            $t->text('owner_reply')->nullable();
            $t->timestamp('owner_replied_at')->nullable();
            $t->enum('status', ['published', 'hidden', 'flagged'])->default('published')->index();
            $t->boolean('is_verified')->default(false);
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['gym_id', 'user_id']);
        });

        Schema::create('review_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('review_id')->constrained('gym_reviews')->cascadeOnDelete();
            $t->string('url');
            $t->string('public_id')->nullable();
            $t->timestamps();
        });

        Schema::create('review_likes', function (Blueprint $t) {
            $t->foreignId('review_id')->constrained('gym_reviews')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->primary(['review_id', 'user_id']);
        });

        Schema::create('review_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('review_id')->constrained('gym_reviews')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('reason');
            $t->text('details')->nullable();
            $t->enum('status', ['pending', 'reviewed', 'dismissed'])->default('pending');
            $t->timestamps();
        });

        Schema::create('favorites', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->timestamp('created_at')->useCurrent();
            $t->primary(['user_id', 'gym_id']);
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('type');
            $t->morphs('notifiable');
            $t->text('data');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('review_reports');
        Schema::dropIfExists('review_likes');
        Schema::dropIfExists('review_images');
        Schema::dropIfExists('gym_reviews');
    }
};
