<?php
/**
 * Builds storefront-do-corpus.json: every `do` value the shop can route to a controller.
 *
 * Usage: php tests/fixtures/generate-do-corpus.php <GX src directory> <source label> > tests/fixtures/storefront-do-corpus.json
 * A controller is any non-abstract class whose parent chain reaches HttpViewController; its route name is the class
 * name without the "Controller" suffix (see EnvironmentHttpViewControllerRegistryFactory in the shop).
 */

$src     = rtrim($argv[1] ?? '', '/');
$label   = $argv[2] ?? $src;
$parents = [];
$isAbstract = [];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getExtension() !== 'php' || strpos($file->getPathname(), '/vendor/') !== false) {
        continue;
    }
    preg_match_all('/^\s*(abstract\s+)?class\s+(\w+)\s+extends\s+([\\\\\w]+)/m', file_get_contents($file), $matches, PREG_SET_ORDER);
    foreach ($matches as [, $abstract, $class, $parent]) {
        $parents[$class] = basename(str_replace('\\', '/', $parent));
        if ($abstract !== '') {
            $isAbstract[$class] = true;
        }
    }
}

$routes = [];
foreach ($parents as $class => $parent) {
    if (isset($isAbstract[$class])) {
        continue;
    }
    for ($ancestor = $parent, $depth = 0; $ancestor !== null && $depth < 30; $ancestor = $parents[$ancestor] ?? null, $depth++) {
        if ($ancestor === 'HttpViewController') {
            $routes[] = substr($class, -10) === 'Controller' ? substr($class, 0, -10) : $class;
            break;
        }
    }
}
sort($routes);

echo json_encode([
    'source'     => $label,
    'regenerate' => 'php tests/fixtures/generate-do-corpus.php <GX src> "<label>" > tests/fixtures/storefront-do-corpus.json',
    'routes'     => array_values(array_unique($routes)),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
