<?php

// Checks the route names used in the Blade views and the controllers used by
// the routes. Run it from the project root: php check-routes.php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = app('router')->getRoutes();
$names = array_keys($routes->getRoutesByName());

$missingRoutes = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/resources/views', FilesystemIterator::SKIP_DOTS));

foreach ($files as $file) {
    if (! str_ends_with($file->getPathname(), '.blade.php')) {
        continue;
    }

    preg_match_all("/route\(\s*'([^']+)'/", file_get_contents($file->getPathname()), $matches);

    foreach (array_unique($matches[1]) as $name) {
        if (! in_array($name, $names, true)) {
            $missingRoutes[$name][] = substr($file->getPathname(), strlen(__DIR__) + 1);
        }
    }
}

$missingControllers = [];

foreach ($routes as $route) {
    $action = $route->getActionName();

    if (str_contains($action, '@')) {
        [$class, $method] = explode('@', $action);

        if (! class_exists($class) || ! method_exists($class, $method)) {
            $missingControllers[] = $action.'  ('.$route->uri().')';
        }
    }
}

echo "Route names used in views but not defined:\n";
echo $missingRoutes === []
    ? "  none\n"
    : implode('', array_map(fn ($name, $in) => "  {$name}  <- ".implode(', ', $in)."\n", array_keys($missingRoutes), $missingRoutes));

echo "\nRoutes whose controller or method does not exist:\n";
echo $missingControllers === [] ? "  none\n" : '  '.implode("\n  ", $missingControllers)."\n";