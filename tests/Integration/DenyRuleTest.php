<?php

namespace GProtector\Tests\Integration;

use GProtector\Tests\Support\EngineRunner;
use PHPUnit\Framework\TestCase;

class DenyRuleTest extends TestCase
{
    private const REFERENCE_RULE = [
        'key'         => 'styleedit-storefront',
        'script_name' => 'shop.php',
        'variables'   => [['type' => 'GET', 'property' => 'do']],
        'pattern'     => '#^StyleEdit4Authentication(?:\W|$)#',
        'action'      => 'deny',
        'function'    => 'filter_text',
        'severity'    => 'error',
    ];
    
    private const LEGIT_ROUTES = [
        'JsTranslations',
        'Cart',
        'Cart/Add',
        'Cart/Update',
        'WishList/Add',
        'PayPal/Webhook',
        'CookieConsentPanelVendorListAjax/List',
    ];
    
    
    private function shippedRules(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../../filter/standard.json'), true);
    }
    
    
    /**
     * @dataProvider blockedValues
     */
    public function testReferenceRuleBlocks(string $do): void
    {
        $runner = new EngineRunner($this->shippedRules(), [self::REFERENCE_RULE]);
        $result = $runner->run('shop.php', ['do' => $do]);
        
        $this->assertTrue($result['blocked'], "Not blocked: $do");
        $this->assertSame('forbidden', $result['output']);
        $logs = $runner->logMessages();
        $this->assertSame('Die Regel "styleedit-storefront" hat eine Anfrage blockiert.', end($logs['security']));
        $this->assertSame('blockierte Anfrage', end($logs['security_debug']));
    }
    
    
    public function blockedValues(): array
    {
        return [
            'baseline'                    => ['StyleEdit4Authentication'],
            'action'                      => ['StyleEdit4Authentication/x'],
            'dot'                         => ['StyleEdit4Authentication.x'],
            'dash'                        => ['StyleEdit4Authentication-foo'],
            'null byte'                   => ["StyleEdit4Authentication\0x"],
            'multibyte'                   => ['StyleEdit4Authenticationé'],
            // filter_text deletes "onload"/"src" once a handler pattern is present; deny sees the result
            'filter_text bypass (onload)' => ['StyleEdit4Authonloadentication/src=x'],
            'filter_text bypass (src)'    => ['StyleEdit4Authsrcentication/x onclick=1'],
        ];
    }
    
    
    public function testLegitRoutesAreUnchanged(): void
    {
        $inputs = [];
        foreach (self::LEGIT_ROUTES as $route) {
            $inputs[] = ['get' => ['do' => $route, 'id' => '12abc'], 'post' => [], 'request' => null];
        }
        
        $without = (new EngineRunner($this->shippedRules()))->runMany('shop.php', $inputs);
        $with    = (new EngineRunner($this->shippedRules(), [self::REFERENCE_RULE]))->runMany('shop.php', $inputs);
        
        $this->assertCount(count(self::LEGIT_ROUTES), $with, 'A legit route was blocked');
        $this->assertSame($without, $with);
    }
    
    
    public function testRuleOnlyCoversItsVariableAndScript(): void
    {
        $runner = new EngineRunner($this->shippedRules(), [self::REFERENCE_RULE]);
        
        $this->assertFalse($runner->run('shop.php', [], ['do' => 'StyleEdit4Authentication'])['blocked'], 'POST');
        $this->assertFalse($runner->run('admin/admin.php', ['do' => 'StyleEdit4Authentication'])['blocked'], 'admin.php');
        $this->assertFalse($runner->run('shop.php', ['other' => 'StyleEdit4Authentication'])['blocked'], 'other variable');
    }
    
    
    public function testArrayValueIsBlockedIfAnyElementMatches(): void
    {
        $runner = new EngineRunner([], [self::REFERENCE_RULE]);
        
        $this->assertTrue($runner->run('shop.php', ['do' => ['Cart', 'StyleEdit4Authentication']])['blocked']);
    }
    
    
    public function testDenyRuleWorksWithoutFunction(): void
    {
        $rule = self::REFERENCE_RULE;
        unset($rule['function']);
        $runner = new EngineRunner([], [$rule]);
        
        $this->assertTrue($runner->run('shop.php', ['do' => 'StyleEdit4Authentication'])['blocked']);
        $this->assertArrayNotHasKey('gprotector_error', $runner->logMessages());
    }
    
    
    public function testSubcategoryVariable(): void
    {
        $rule = ['variables' => [['type' => 'POST', 'property' => ['name'], 'subcategory' => 'form']]] + self::REFERENCE_RULE;
        $runner = new EngineRunner([], [$rule]);
        
        $this->assertTrue($runner->run('shop.php', [], ['form' => ['name' => 'StyleEdit4Authentication']])['blocked']);
        $this->assertFalse($runner->run('shop.php', [], ['form' => ['name' => 'Cart']])['blocked']);
    }
    
    
    public function testSanitizeRuleWithPatternOnlyTouchesMatchingValues(): void
    {
        $runner = new EngineRunner([], [
            [
                'key'         => 'digits-first',
                'script_name' => 'shop.php',
                'variables'   => [['type' => 'GET', 'property' => 'id']],
                'pattern'     => '#^\d#',
                'function'    => 'convert_to_integer',
                'severity'    => 'error',
            ],
        ]);
        
        $this->assertSame('12', $runner->run('shop.php', ['id' => '12abc'])['get']['id']);
        $this->assertSame('abc12', $runner->run('shop.php', ['id' => 'abc12'])['get']['id']);
        $this->assertSame(['1', 'b2', ['3']], $runner->run('shop.php', ['id' => ['1a', 'b2', ['3c']]])['get']['id']);
    }
    
    
    public function testInvalidRulesAreSkippedAndLogged(): void
    {
        $sanitize = [
            'key'         => 'probe',
            'script_name' => 'shop.php',
            'variables'   => [['type' => 'GET', 'property' => 'probe']],
            'function'    => 'convert_to_integer',
            'severity'    => 'error',
        ];
        $runner   = new EngineRunner([], [], [
            ['key' => 'malformed', 'pattern' => '#^StyleEdit('] + self::REFERENCE_RULE,
            ['key' => 'matches-everything', 'pattern' => '#.*#'] + self::REFERENCE_RULE,
            $sanitize,
        ]);
        
        $result = $runner->run('shop.php', ['do' => 'StyleEdit4Authentication', 'probe' => '12abc']);
        
        $this->assertFalse($result['blocked']);
        $this->assertSame('12', $result['get']['probe'], 'The valid rule must still apply');
        $this->assertSame([
            'Die Regel "malformed" wurde übersprungen: Invalid $pattern',
            'Die Regel "matches-everything" wurde übersprungen: The $pattern of a deny rule must not match an empty value',
        ], $runner->logMessages()['gprotector_error']);
    }
    
    
    public function testCachedRulesetWithNewFieldsIsAccepted(): void
    {
        $rule = self::REFERENCE_RULE;
        unset($rule['function']);
        $runner = new EngineRunner([], [], array_merge($this->shippedRules(), [$rule]));
        
        $this->assertTrue($runner->run('shop.php', ['do' => 'StyleEdit4Authentication'])['blocked']);
    }
    
    
    public function testCachedRulesetWithWrongFieldTypeFallsBackToShippedRules(): void
    {
        $runner = new EngineRunner($this->shippedRules(), [], [['pattern' => ['not a string']] + self::REFERENCE_RULE]);
        $result = $runner->run('shop.php', ['do' => 'StyleEdit4Authentication', 'categories_id' => '12abc']);
        
        $this->assertFalse($result['blocked'], 'The rejected cache must not be used');
        $this->assertSame('12', $result['get']['categories_id'], 'The shipped rules must be used instead');
    }
}
