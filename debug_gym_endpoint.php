<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $repo = $app->make(App\Repositories\Contracts\GymRepositoryInterface::class);
    $result = $repo->search(['sort' => 'rating'], 6);

    $payload = [
        'success' => true,
        'message' => 'OK',
        'data' => [
            'items' => App\Http\Resources\GymResource::collection($result->items())->resolve(),
            'meta' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
        ],
    ];

    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, get_class($e).PHP_EOL);
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    fwrite(STDERR, $e->getFile().':'.$e->getLine().PHP_EOL);
    fwrite(STDERR, $e->getTraceAsString().PHP_EOL);
    exit(1);
}
