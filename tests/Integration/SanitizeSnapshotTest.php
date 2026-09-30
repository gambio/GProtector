<?php

namespace GProtector\Tests\Integration;

use GProtector\Tests\Support\EngineRunner;
use PHPUnit\Framework\TestCase;

/**
 * Every shipped sanitize rule must keep producing byte-identical output.
 *
 * Input: a frozen copy of the ruleset (fixtures/snapshot-rules.json). For every script, every variable of every
 * rule gets the same sample value, per sample. The expected result was recorded with the engine before
 * pattern/action support existed. Re-record only for an intended behaviour change:
 * GPROTECTOR_UPDATE_SNAPSHOT=1 vendor/bin/phpunit tests/Integration/SanitizeSnapshotTest.php
 */
class SanitizeSnapshotTest extends TestCase
{
    private const SAMPLES = [
        'plain'     => 'abc',
        'digits'    => '123',
        'mixed'     => '12abc',
        'decimal'   => '-1,5.25',
        'script'    => '<script>alert(1)</script>',
        'handler'   => 'a" onload=x src=y',
        'route'     => 'Cart/Add',
        'quotes'    => "O'Brien & Co <b>",
        'path'      => '../../etc/passwd',
        'url'       => 'http://evil.example/x?y=1',
        'umlauts'   => 'Grüße ßäöü €',
        'empty'     => '',
        // last: some functions fatal on arrays (pre-existing), which ends the run for that script
        'array'     => ['1', '<b>x</b>', ['nested' => '2a']],
    ];
    
    
    public function testShippedRulesProduceRecordedOutput(): void
    {
        $rules  = json_decode(file_get_contents(__DIR__ . '/../fixtures/snapshot-rules.json'), true);
        $runner = new EngineRunner($rules);
        $actual = [];
        
        foreach ($this->scripts($rules) as $script) {
            $inputs = [];
            foreach (self::SAMPLES as $sample) {
                $inputs[] = $this->inputFor($rules, $script, $sample);
            }
            $results = $runner->runMany($script, $inputs);
            foreach (array_keys(self::SAMPLES) as $index => $name) {
                $actual[$script][$name] = $results[$index] ?? null;
            }
        }
        
        $snapshotFile = __DIR__ . '/../fixtures/sanitize-snapshot.json';
        if (getenv('GPROTECTOR_UPDATE_SNAPSHOT')) {
            file_put_contents($snapshotFile, json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->markTestSkipped('Snapshot re-recorded');
        }
        
        $expected = json_decode(file_get_contents($snapshotFile), true);
        $this->assertSame(array_keys($expected), array_keys($actual), 'Scripts differ from the snapshot');
        foreach ($expected as $script => $samples) {
            foreach ($samples as $name => $result) {
                $this->assertSame($result, $actual[$script][$name], "Output changed for $script / sample \"$name\"");
            }
        }
    }
    
    
    private function scripts(array $rules): array
    {
        $scripts = [];
        foreach ($rules as $rule) {
            foreach ((array)$rule['script_name'] as $script) {
                $scripts[$script] = true;
            }
        }
        ksort($scripts);
        
        return array_keys($scripts);
    }
    
    
    private function inputFor(array $rules, string $script, $sample): array
    {
        $input = ['get' => [], 'post' => [], 'request' => []];
        foreach ($rules as $rule) {
            if (!in_array($script, (array)$rule['script_name'], true)) {
                continue;
            }
            foreach ($rule['variables'] as $variable) {
                $type = strtolower($variable['type']);
                foreach ((array)$variable['property'] as $property) {
                    if (isset($variable['subcategory']) && is_array($variable['property'])) {
                        $input[$type][$variable['subcategory']][$property] = $sample;
                    } else {
                        $input[$type][$property] = $sample;
                    }
                }
            }
        }
        
        return $input;
    }
}
