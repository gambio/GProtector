<?php

namespace GProtector\Tests\Support;

use RuntimeException;

/**
 * Runs the real engine (start.inc.php) in a subprocess inside a throw-away shop directory.
 *
 * config.inc.php derives the log, token and cache paths from its own location, so every run
 * works on a copy. The remote rule URL points at a closed local port: no network access.
 */
final class EngineRunner
{
    private $root;
    
    
    /**
     * @param array      $standardRules rules for filter/standard.json (the fallback set)
     * @param array      $customRules   rules for an extra filter/*.json file
     * @param array|null $cachedRules   rules for cache/standard.json, null for no cache file
     */
    public function __construct(array $standardRules, array $customRules = [], ?array $cachedRules = null)
    {
        $source     = dirname(__DIR__, 2);
        $this->root = sys_get_temp_dir() . '/gprotector-test-' . bin2hex(random_bytes(6));
        
        foreach (['GProtector/classes', 'GProtector/functions', 'GProtector/filter', 'GProtector/cache', 'logfiles', 'media', 'vendor'] as $dir) {
            mkdir($this->root . '/' . $dir, 0777, true);
        }
        
        foreach (glob($source . '/classes/*.php') as $file) {
            copy($file, $this->root . '/GProtector/classes/' . basename($file));
        }
        foreach (glob($source . '/functions/*.php') as $file) {
            copy($file, $this->root . '/GProtector/functions/' . basename($file));
        }
        copy($source . '/start.inc.php', $this->root . '/GProtector/start.inc.php');
        file_put_contents($this->root . '/GProtector/config.inc.php',
                          str_replace('https://protect.gambio-server.net/standard.json',
                                      'http://127.0.0.1:9/standard.json',
                                      file_get_contents($source . '/config.inc.php')));
        file_put_contents($this->root . '/vendor/autoload.php', "<?php\n");
        
        file_put_contents($this->root . '/GProtector/filter/standard.json', json_encode($standardRules));
        if ($customRules !== []) {
            file_put_contents($this->root . '/GProtector/filter/custom.json', json_encode($customRules));
        }
        if ($cachedRules !== null) {
            file_put_contents($this->root . '/GProtector/cache/standard.json', json_encode($cachedRules));
        }
    }
    
    
    public function __destruct()
    {
        $this->removeDirectory($this->root);
    }
    
    
    /**
     * Runs the engine once as if $script (e.g. "shop.php", "admin/orders.php") was requested.
     *
     * @return array{blocked: bool, get: array, post: array, request: array, output: string}
     */
    public function run(string $script, array $get = [], array $post = [], ?array $request = null): array
    {
        $states = $this->runMany($script, [['get' => $get, 'post' => $post, 'request' => $request]]);
        
        return $states[0];
    }
    
    
    /**
     * Runs the engine once per input inside a single process. The first input goes through
     * start.inc.php; every further input builds a fresh GProtector exactly like start.inc.php does.
     * A deny or a fatal error ends the process: its result carries the output, later inputs are not run.
     *
     * @param array $inputs list of ['get' => [], 'post' => [], 'request' => array|null]
     *
     * @return array list of results, see run()
     */
    public function runMany(string $script, array $inputs): array
    {
        $scriptPath = $this->root . '/' . $script;
        if (!is_dir(dirname($scriptPath))) {
            mkdir(dirname($scriptPath), 0777, true);
        }
        $startFile = str_repeat('/..', substr_count($script, '/')) . '/GProtector/start.inc.php';
        file_put_contents($scriptPath, <<<PHP
<?php
\$inputs    = json_decode(file_get_contents(__DIR__ . '/input.json'), true);
\$stateFile = __DIR__ . '/state.json';
file_put_contents(\$stateFile, '[]');
\$prepare = function (array \$input) {
    \$_GET     = \$input['get'];
    \$_POST    = \$input['post'];
    \$_REQUEST = \$input['request'] ?? array_merge(\$input['get'], \$input['post']);
    \$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
};
\$record = function () use (\$stateFile) {
    \$states   = json_decode(file_get_contents(\$stateFile), true);
    \$states[] = ['get' => \$_GET, 'post' => \$_POST, 'request' => \$_REQUEST];
    file_put_contents(\$stateFile, json_encode(\$states));
};
\$prepare(array_shift(\$inputs));
require __DIR__ . '$startFile';
\$record();
foreach (\$inputs as \$input) {
    \$prepare(\$input);
    (new GProtector\GProtector(new GProtector\FilterReader(), new GProtector\FilterCache()))->start();
    \$record();
}
PHP
        );
        file_put_contents(dirname($scriptPath) . '/input.json', json_encode($inputs));
        
        $command = [PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'error_reporting=' . (E_ALL & ~E_DEPRECATED), $scriptPath];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Could not start PHP');
        }
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        $results = [];
        foreach (json_decode(file_get_contents(dirname($scriptPath) . '/state.json'), true) as $state) {
            $results[] = $state + ['blocked' => false, 'output' => ''];
        }
        if (preg_match('/^(?:PHP )?Fatal error:\s+(.*)$/m', $stderr, $fatal)) {
            $message   = preg_replace('/ in \S+:\d+$/', '', $fatal[1]);
            $results[] = ['blocked' => false, 'output' => 'fatal: ' . $message, 'get' => null, 'post' => null, 'request' => null];
        } elseif ($stdout !== '') {
            $results[] = ['blocked' => $stdout === 'forbidden', 'output' => $stdout, 'get' => null, 'post' => null, 'request' => null];
        }
        
        return $results;
    }
    
    
    /**
     * Log messages written so far, keyed by log type (e.g. "security", "gprotector_error").
     *
     * @return array<string, string[]>
     */
    public function logMessages(): array
    {
        $messages = [];
        foreach (glob($this->root . '/logfiles/*.log') as $file) {
            $type = explode('-', basename($file))[0];
            preg_match_all('/^Nachricht: ([^\r\n]*)/m', file_get_contents($file), $matches);
            $messages[$type] = array_merge($messages[$type] ?? [], $matches[1]);
        }
        
        return $messages;
    }
    
    
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (array_diff(scandir($dir), ['.', '..']) as $entry) {
            is_dir("$dir/$entry") ? $this->removeDirectory("$dir/$entry") : unlink("$dir/$entry");
        }
        rmdir($dir);
    }
}
