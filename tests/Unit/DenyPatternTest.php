<?php

namespace GProtector\Tests\Unit;

use GProtector\Pattern;
use GProtector\Tests\Support\RouterOracle;
use PHPUnit\Framework\TestCase;

/**
 * A deny rule for controller C must block a `do` value exactly when the shop routes it to C.
 * False negative: routed to C but not blocked. False positive: blocked but routed elsewhere.
 */
class DenyPatternTest extends TestCase
{
    private const TARGET            = 'StyleEdit4Authentication';
    private const REFERENCE_PATTERN = '#^StyleEdit4Authentication(?:\W|$)#';
    private const RANDOM_SEED       = 20260925;
    
    
    protected function setUp(): void
    {
        // \w and \W follow LC_CTYPE; the shop core never changes it
        setlocale(LC_CTYPE, 'C');
    }
    
    
    /**
     * @dataProvider curatedValues
     */
    public function testReferencePatternOnCuratedValues(string $value, bool $expectBlock): void
    {
        $routesToTarget = RouterOracle::controllerName($value) === self::TARGET;
        $this->assertSame($expectBlock, $routesToTarget, 'Test data disagrees with the router');
        $this->assertSame($routesToTarget, (new Pattern(self::REFERENCE_PATTERN))->matches($value));
    }
    
    
    public function curatedValues(): array
    {
        $block = [
            'baseline'               => self::TARGET,
            'action slash'           => self::TARGET . '/',
            'action'                 => self::TARGET . '/x',
            'nested action'          => self::TARGET . '/Foo/Bar',
            'dot'                    => self::TARGET . '.x',
            'dash'                   => self::TARGET . '-x',
            'question mark'          => self::TARGET . '?x',
            'hash'                   => self::TARGET . '#x',
            'literal %2F'            => self::TARGET . '%2Fx',
            'space'                  => self::TARGET . ' x',
            'tab'                    => self::TARGET . "\tx",
            'null byte'              => self::TARGET . "\0x",
            'multibyte'              => self::TARGET . 'é',
            'trailing newline'       => self::TARGET . "\n",
            'invalid utf-8'          => self::TARGET . "\xff",
        ];
        $pass  = [
            'lowercase'              => 'styleedit4authentication',
            'uppercase'              => 'STYLEEDIT4AUTHENTICATION',
            'letter suffix'          => self::TARGET . 'X',
            'digit suffix'           => self::TARGET . '4',
            'underscore suffix'      => self::TARGET . '_x',
            'letter prefix'          => 'X' . self::TARGET,
            'underscore prefix'      => '_' . self::TARGET,
            'leading space'          => ' ' . self::TARGET,
            'leading slash'          => '/' . self::TARGET,
            'StyleEdit'              => 'StyleEdit',
            'StyleEdit4'             => 'StyleEdit4',
            'StyleEdit3'             => 'StyleEdit3Authentication',
            'StyleEdit5'             => 'StyleEdit5Authentication',
            'JsTranslations'         => 'JsTranslations',
            'Cart'                   => 'Cart',
            'Cart/Add'               => 'Cart/Add',
            'Cart/Update'            => 'Cart/Update',
            'WishList/Add'           => 'WishList/Add',
            'PayPal/Webhook'         => 'PayPal/Webhook',
            'CookieConsent list'     => 'CookieConsentPanelVendorListAjax/List',
            'empty'                  => '',
        ];
        
        $cases = [];
        foreach ($block as $name => $value) {
            $cases["block: $name"] = [$value, true];
        }
        foreach ($pass as $name => $value) {
            $cases["pass: $name"] = [$value, false];
        }
        
        return $cases;
    }
    
    
    public function testReferencePatternOnGeneratedValues(): void
    {
        $disagreements = $this->disagreements(self::REFERENCE_PATTERN);
        $this->assertSame([], array_slice($disagreements, 0, 10), 'seed ' . self::RANDOM_SEED);
    }
    
    
    /**
     * The suite must be able to tell a wrong pattern from the right one.
     *
     * @dataProvider wrongPatterns
     */
    public function testWrongPatternIsCaught(string $pattern, string $expectedCatch): void
    {
        $disagreements = $this->disagreements($pattern);
        $this->assertNotEmpty($disagreements, "The suite does not catch $pattern");
        $this->assertContains($expectedCatch, $disagreements);
    }
    
    
    public function wrongPatterns(): array
    {
        return [
            'exact match'         => ['#^StyleEdit4Authentication$#', 'false negative: "StyleEdit4Authentication/x"'],
            'no end boundary'     => ['#^StyleEdit4Authentication#', 'false positive: "StyleEdit4AuthenticationX"'],
            'no start anchor'     => ['#StyleEdit4Authentication(?:\W|$)#', 'false positive: "XStyleEdit4Authentication"'],
            'case-insensitive'    => ['#^StyleEdit4Authentication(?:\W|$)#i', 'false positive: "styleedit4authentication"'],
            'ticket draft'        => ['#^StyleEdit\d*Authentication#i', 'false positive: "StyleEdit5Authentication"'],
            'unicode mode'        => ['#^StyleEdit4Authentication(?:\W|$)#u', "false negative: \"StyleEdit4Authentication\xff\""],
        ];
    }
    
    
    /**
     * Compares the pattern with the router on curated and generated values.
     *
     * @return string[] one entry per value where pattern and router disagree
     */
    private function disagreements(string $pattern): array
    {
        $pattern = new Pattern($pattern);
        $result  = [];
        foreach ($this->corpus() as $value) {
            $routesToTarget = RouterOracle::controllerName($value) === self::TARGET;
            if ($pattern->matches($value) !== $routesToTarget) {
                $result[] = ($routesToTarget ? 'false negative: "' : 'false positive: "') . $value . '"';
            }
        }
        
        return $result;
    }
    
    
    private function corpus(): array
    {
        $values = array_column($this->curatedValues(), 0);
        
        for ($byte = 0; $byte < 256; $byte++) {
            $values[] = self::TARGET . chr($byte) . 'x';
            $values[] = chr($byte) . self::TARGET;
        }
        
        for ($i = 0, $length = strlen(self::TARGET); $i < $length; $i++) {
            $flipped = self::TARGET;
            $flipped[$i] = ctype_upper($flipped[$i]) ? strtolower($flipped[$i]) : strtoupper($flipped[$i]);
            $values[] = $flipped;
        }
        
        mt_srand(self::RANDOM_SEED);
        $fragments = ['StyleEdit', '4', 'Authentication', self::TARGET, '/', '.', '-', '%', ' ', '_', 'x', 'X', '0'];
        for ($i = 0; $i < 5000; $i++) {
            $value = '';
            for ($parts = mt_rand(1, 4); $parts > 0; $parts--) {
                $value .= $fragments[mt_rand(0, count($fragments) - 1)];
            }
            $values[] = $value;
        }
        
        return array_values(array_unique($values));
    }
}
