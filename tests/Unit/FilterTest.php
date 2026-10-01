<?php

namespace GProtector\Tests\Unit;

use GProtector\Action;
use GProtector\Filter;
use GProtector\FilterCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    private const DENY_RULE = [
        'key'         => 'deny-rule',
        'script_name' => 'shop.php',
        'variables'   => [['type' => 'GET', 'property' => 'do']],
        'pattern'     => '#^StyleEdit4Authentication(?:\W|$)#',
        'action'      => 'deny',
        'severity'    => 'error',
    ];
    
    private const SANITIZE_RULE = [
        'key'         => 'sanitize-rule',
        'script_name' => 'shop.php',
        'variables'   => [['type' => 'GET', 'property' => 'id']],
        'function'    => 'convert_to_integer',
        'severity'    => 'error',
    ];
    
    
    public function testRuleWithoutNewFieldsKeepsItsMeaning(): void
    {
        $filter = Filter::fromData(self::SANITIZE_RULE);
        
        $this->assertSame(Action::SANITIZE, $filter->action());
        $this->assertNull($filter->pattern());
        $this->assertSame('convert_to_integer', $filter->method());
    }
    
    
    public function testDenyRuleNeedsNoFunction(): void
    {
        $filter = Filter::fromData(self::DENY_RULE);
        
        $this->assertSame(Action::DENY, $filter->action());
        $this->assertNull($filter->method());
        $this->assertSame(self::DENY_RULE['pattern'], $filter->pattern()->pattern());
    }
    
    
    public function testDenyRuleKeepsCompatFunction(): void
    {
        $this->assertSame('filter_text', Filter::fromData(self::DENY_RULE + ['function' => 'filter_text'])->method());
    }
    
    
    public function testSanitizeRuleCanHavePattern(): void
    {
        $filter = Filter::fromData(self::SANITIZE_RULE + ['pattern' => '#^\d#']);
        
        $this->assertSame(Action::SANITIZE, $filter->action());
        $this->assertTrue($filter->pattern()->matches('1a'));
        $this->assertFalse($filter->pattern()->matches('a1'));
    }
    
    
    /**
     * @dataProvider invalidRules
     */
    public function testInvalidRuleIsRejected(array $rule, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        
        Filter::fromData($rule);
    }
    
    
    public function invalidRules(): array
    {
        return [
            'malformed pattern'         => [['pattern' => '#^StyleEdit('] + self::DENY_RULE, 'Invalid $pattern'],
            'pattern without delimiter' => [['pattern' => 'StyleEdit'] + self::DENY_RULE, 'Invalid $pattern'],
            'pattern not a string'      => [['pattern' => ['#x#']] + self::DENY_RULE, 'Invalid $pattern'],
            'unknown action'            => [['action' => 'block'] + self::DENY_RULE, 'Invalid $action'],
            'deny without pattern'      => [array_diff_key(self::DENY_RULE, ['pattern' => 0]), 'needs a $pattern'],
            'deny matching empty value' => [['pattern' => '#.*#'] + self::DENY_RULE, 'must not match an empty value'],
            'sanitize without function' => [array_diff_key(self::SANITIZE_RULE, ['function' => 0]), 'Invalid $method'],
        ];
    }
    
    
    public function testCollectionSkipsInvalidRuleWhenAsked(): void
    {
        $skipped    = [];
        $collection = FilterCollection::fromData(
            [self::SANITIZE_RULE, ['pattern' => '#('] + self::DENY_RULE, self::DENY_RULE],
            function ($rawFilter, InvalidArgumentException $exception) use (&$skipped) {
                $skipped[] = $rawFilter['key'] . ': ' . $exception->getMessage();
            }
        );
        
        $keys = array_map(function (Filter $filter) {
            return $filter->key();
        }, iterator_to_array($collection));
        $this->assertSame(['sanitize-rule', 'deny-rule'], $keys);
        $this->assertSame(['deny-rule: Invalid $pattern'], $skipped);
    }
    
    
    public function testCollectionThrowsWithoutCallback(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        FilterCollection::fromData([self::SANITIZE_RULE, ['pattern' => '#('] + self::DENY_RULE]);
    }
}
