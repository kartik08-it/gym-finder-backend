<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->string('icon')->nullable();          // lucide icon name
            $t->string('category')->nullable();      // equipment | services | facility
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('gyms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('city_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->text('address');
            $t->string('area')->nullable();
            $t->string('postal_code', 12)->nullable();
            $t->decimal('latitude', 10, 7);
            $t->decimal('longitude', 10, 7);
            $t->string('phone', 20)->nullable();
            $t->string('email')->nullable();
            $t->string('website')->nullable();
            $t->string('cover_image')->nullable();
            $t->time('opening_time')->default('06:00:00');
            $t->time('closing_time')->default('22:00:00');
            $t->boolean('is_24x7')->default(false);
            $t->boolean('ladies_only')->default(false);
            $t->enum('gender_preference', ['unisex', 'male', 'female'])->default('unisex');
            $t->unsignedInteger('trainer_count')->default(0);
            $t->enum('crowd_level', ['low', 'medium', 'high'])->default('medium');
            $t->json('peak_hours')->nullable();
            $t->decimal('starting_price', 10, 2)->default(0);
            $t->decimal('rating_avg', 3, 2)->default(0)->index();
            $t->unsignedInteger('rating_count')->default(0);
            $t->unsignedInteger('view_count')->default(0);
            $t->enum('status', ['draft', 'pending', 'approved', 'rejected', 'suspended'])
              ->default('pending')->index();
            $t->text('rejection_reason')->nullable();
            $t->boolean('is_verified')->default(false)->index();
            $t->boolean('is_featured')->default(false)->index();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['latitude', 'longitude']);
            $t->index(['city_id', 'status']);
            $t->fullText(['name', 'description', 'address']);
        });

        Schema::create('gym_amenity', function (Blueprint $t) {
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $t->primary(['gym_id', 'amenity_id']);
        });

        Schema::create('gym_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->string('url');
            $t->string('public_id')->nullable();     // Cloudinary
            $t->string('caption')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['gym_id', 'sort_order']);
        });

        Schema::create('gym_videos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->string('url');
            $t->string('thumbnail_url')->nullable();
            $t->string('title')->nullable();
            $t->timestamps();
        });

        Schema::create('gym_trainers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('avatar_url')->nullable();
            $t->enum('gender', ['male', 'female', 'other'])->default('male');
            $t->unsignedTinyInteger('experience_years')->default(0);
            $t->string('specialization')->nullable();
            $t->text('bio')->nullable();
            $t->decimal('rating_avg', 3, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_trainers');
        Schema::dropIfExists('gym_videos');
        Schema::dropIfExists('gym_images');
        Schema::dropIfExists('gym_amenity');
        Schema::dropIfExists('gyms');
        Schema::dropIfExists('amenities');
    }
};
