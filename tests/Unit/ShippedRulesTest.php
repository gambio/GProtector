<?php

namespace GProtector\Tests\Unit;

use GProtector\Action;
use GProtector\Filter;
use GProtector\Tests\Support\RouterOracle;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Lint for deny rules before they are published through filter/standard.json.
 *
 * Every deny rule needs an entry in fixtures/deny-rule-cases.json listing values it must block.
 * A deny rule on GET/REQUEST "do" of shop.php may only block the controllers named by those values
 * (checked against every route in fixtures/storefront-do-corpus.json).
 */
class ShippedRulesTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../functions/main.inc.php';
    }
    
    
    public function testShippedDenyRulesPassLint(): void
    {
        $this->assertSame([], $this->lint($this->readRules(__DIR__ . '/../../filter/standard.json')));
    }
    
    
    public function testExampleDenyRulePassesLint(): void
    {
        $rules = $this->readRules(__DIR__ . '/../fixtures/deny-rules-example.json');
        
        $this->assertCount(1, $rules);
        $this->assertSame([], $this->lint($rules));
    }
    
    
    /**
     * @dataProvider badRules
     */
    public function testLintCatchesBadRule(array $change, string $expectedProblem): void
    {
        $rule = array_merge($this->readRules(__DIR__ . '/../fixtures/deny-rules-example.json')[0], $change);
        $rule = array_filter($rule, function ($value) {
            return $value !== null;
        });
        
        $this->assertContains($expectedProblem, $this->lint([$rule]));
    }
    
    
    public function badRules(): array
    {
        return [
            'blocks a legit controller' => [['pattern' => '#^(?:StyleEdit4Authentication|Cart)(?:\W|$)#'], 'styleedit-storefront-auth: blocks route "Cart"'],
            'too broad'                 => [['pattern' => '#^C#'], 'styleedit-storefront-auth: blocks route "Cart"'],
            'misses its own case'       => [['pattern' => '#^StyleEdit4Authentication$#'], 'styleedit-storefront-auth: does not block "StyleEdit4Authentication/x"'],
            'no start anchor'           => [['pattern' => '#StyleEdit4Authentication(?:\W|$)#'], 'styleedit-storefront-auth: pattern must start with ^'],
            'modifier i'                => [['pattern' => '#^StyleEdit4Authentication(?:\W|$)#i'], 'styleedit-storefront-auth: pattern modifiers i/u change the router match'],
            'no function'               => [['function' => null], 'styleedit-storefront-auth: needs a function so older shops accept the file'],
            'unknown function'          => [['function' => 'does_not_exist'], 'styleedit-storefront-auth: function gprotector_does_not_exist does not exist'],
            'no cases'                  => [['key' => 'unlisted'], 'unlisted: no entry in deny-rule-cases.json'],
            'invalid'                   => [['pattern' => '#.*#'], 'styleedit-storefront-auth: The $pattern of a deny rule must not match an empty value'],
        ];
    }
    
    
    /**
     * @return string[] problems, empty if all deny rules are fine
     */
    private function lint(array $rules): array
    {
        $cases    = json_decode(file_get_contents(__DIR__ . '/../fixtures/deny-rule-cases.json'), true);
        $routes   = json_decode(file_get_contents(__DIR__ . '/../fixtures/storefront-do-corpus.json'), true)['routes'];
        $problems = [];
        
        foreach ($rules as $rule) {
            if (($rule['action'] ?? Action::SANITIZE) !== Action::DENY) {
                continue;
            }
            $key = $rule['key'];
            
            try {
                $filter = Filter::fromData($rule);
            } catch (InvalidArgumentException $e) {
                $problems[] = "$key: " . $e->getMessage();
                continue;
            }
            
            $pattern   = $filter->pattern()->pattern();
            $delimiter = $pattern[0];
            if (($pattern[1] ?? '') !== '^') {
                $problems[] = "$key: pattern must start with ^";
            }
            if (strpbrk(substr($pattern, strrpos($pattern, $delimiter) + 1), 'iu') !== false) {
                $problems[] = "$key: pattern modifiers i/u change the router match";
            }
            if ($filter->method() === null) {
                $problems[] = "$key: needs a function so older shops accept the file";
            } elseif (!function_exists('gprotector_' . $filter->method())) {
                $problems[] = "$key: function gprotector_{$filter->method()} does not exist";
            }
            
            if (!isset($cases[$key])) {
                $problems[] = "$key: no entry in deny-rule-cases.json";
                continue;
            }
            foreach ($cases[$key] as $value) {
                if (!$filter->pattern()->matches($value)) {
                    $problems[] = "$key: does not block \"$value\"";
                }
            }
            
            if ($this->coversShopDo($rule)) {
                $allowed = array_map([RouterOracle::class, 'controllerName'], $cases[$key]);
                foreach ($routes as $route) {
                    foreach ([$route, "$route/x"] as $value) {
                        if ($filter->pattern()->matches($value) && !in_array(RouterOracle::controllerName($value), $allowed, true)) {
                            $problems[] = "$key: blocks route \"$value\"";
                        }
                    }
                }
            }
        }
        
        return array_values(array_unique($problems));
    }
    
    
    private function coversShopDo(array $rule): bool
    {
        if (!in_array('shop.php', (array)$rule['script_name'], true)) {
            return false;
        }
        foreach ($rule['variables'] as $variable) {
            if (in_array(strtoupper($variable['type']), ['GET', 'REQUEST'], true) && in_array('do', (array)$variable['property'], true)) {
                return true;
            }
        }
        
        return false;
    }
    
    
    private function readRules(string $file): array
    {
        return json_decode(file_get_contents($file), true);
    }
}
