<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('phone', 20)->nullable()->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->timestamp('phone_verified_at')->nullable();
            $t->string('password')->nullable();
            $t->enum('role', ['customer', 'gym_owner', 'admin'])->default('customer')->index();
            $t->enum('status', ['active', 'suspended', 'pending'])->default('active')->index();
            $t->string('avatar_url')->nullable();
            $t->enum('gender', ['male', 'female', 'other'])->nullable();
            $t->date('date_of_birth')->nullable();
            $t->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $t->string('provider')->nullable();          // google | phone | email
            $t->string('provider_id')->nullable();
            $t->string('fcm_token')->nullable();
            $t->rememberToken();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });

        Schema::create('otp_verifications', function (Blueprint $t) {
            $t->id();
            $t->string('identifier')->index();      // phone or email
            $t->enum('channel', ['sms', 'email']);
            $t->string('code_hash');
            $t->timestamp('expires_at');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->timestamp('used_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
