<?php

namespace App\Providers;

use App\Repositories\Contracts\GymRepositoryInterface;
use App\Repositories\Eloquent\GymRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        GymRepositoryInterface::class => GymRepository::class,
    ];

    public function register(): void {}

    public function boot(): void {}
}
