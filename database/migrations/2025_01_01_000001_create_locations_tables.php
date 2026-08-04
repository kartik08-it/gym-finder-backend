<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('iso2', 2)->unique();
            $t->string('phone_code', 8)->nullable();
            $t->timestamps();
        });

        Schema::create('states', function (Blueprint $t) {
            $t->id();
            $t->foreignId('country_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code', 16)->nullable();
            $t->timestamps();
            $t->index(['country_id', 'name']);
        });

        Schema::create('cities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('state_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['state_id', 'name']);
            $t->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
