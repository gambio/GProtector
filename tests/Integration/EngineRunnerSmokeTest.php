<?php

namespace GProtector\Tests\Integration;

use GProtector\Tests\Support\EngineRunner;
use PHPUnit\Framework\TestCase;

class EngineRunnerSmokeTest extends TestCase
{
    public function testSanitizeRuleAppliesOnlyToItsScript(): void
    {
        $runner = new EngineRunner([
            [
                'key'         => 'probe',
                'script_name' => 'shop.php',
                'variables'   => [['type' => 'GET', 'property' => 'probe']],
                'function'    => 'convert_to_integer',
                'severity'    => 'error',
            ],
        ]);
        
        $this->assertSame('12', $runner->run('shop.php', ['probe' => '12abc'])['get']['probe']);
        $this->assertSame('12abc', $runner->run('admin/orders.php', ['probe' => '12abc'])['get']['probe']);
        $this->assertNotEmpty($runner->logMessages()['security'] ?? []);
    }
}
