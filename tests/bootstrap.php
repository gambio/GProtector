<?php

require_once __DIR__ . '/../vendor/autoload.php';

foreach (glob(__DIR__ . '/../classes/*.php') as $classFile) {
    require_once $classFile;
}

require_once __DIR__ . '/Support/EngineRunner.php';
require_once __DIR__ . '/Support/RouterOracle.php';
