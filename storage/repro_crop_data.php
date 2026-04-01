<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('role', 'admin')->first() ?? App\Models\User::first();
    if (!$user) {
        throw new RuntimeException('No user found in database');
    }

    Illuminate\Support\Facades\Auth::login($user);

    $request = Illuminate\Http\Request::create('/admin/crop-data', 'GET');
    $controller = app(App\Http\Controllers\CropDataController::class);
    $view = $controller->index($request);

    echo 'View class: ' . get_class($view) . PHP_EOL;
    echo 'Rendered length: ' . strlen($view->render()) . PHP_EOL;
    echo 'Render succeeded' . PHP_EOL;
} catch (Throwable $e) {
    echo 'Exception: ' . get_class($e) . PHP_EOL;
    echo 'Message: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
