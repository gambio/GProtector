<?php

use PHPUnit\Framework\TestCase;

/**
 * Covers gprotector_block_all_urls_in_registration_form(), the anti-spam filter that runs on the registration and
 * guest checkout form. On a hit it silently empties the whole form, so a false positive costs the buyer their order.
 */
class RegistrationFormUrlFilterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../functions/main.inc.php';
    }


    protected function setUp(): void
    {
        $_GET  = ['do' => 'CreateRegistree/Proceed'];
        $_POST = [
            'firstname'      => 'Erika',
            'lastname'       => 'Mustermann',
            'email_address'  => 'erika@example.org',
            'street_address' => 'Hauptstraße 1',
            'postcode'       => '8413',
            'city'           => 'Graz',
        ];
    }


    protected function tearDown(): void
    {
        $_GET  = [];
        $_POST = [];
    }


    /**
     * @dataProvider legitimateInputProvider
     */
    public function testLegitimateInputPassesAndKeepsTheForm(string $input): void
    {
        $_POST['city'] = $input;

        $this->assertSame($input, gprotector_block_all_urls_in_registration_form($input));
        $this->assertSame('Erika', $_POST['firstname']);
        $this->assertSame($input, $_POST['city']);
    }


    public function legitimateInputProvider(): array
    {
        $inputs = [
            // Hyphenated place names followed by a dot abbreviation: the hyphen must not count towards the
            // minimum length of the label in front of the dot.
            'Söding-St.Johann',
            'Söding-St.Jo',
            'Neumarkt-St.Veit',
            'Klein-St.Veit',
            'St.Johann-Köppling',
            'Feld-Weg.Ost',
            // Plain address abbreviations.
            'St.Gallen',
            'St.Johann',
            'Bad St.Leonhard',
            'Krems a.d.Donau',
            'a.d.Th.',
            'zHd.XXY',
            'Nr.5',
            'z.B.',
            '1. OG / Hinterhaus',
            'Musterstr.5',
            'Max-Planck-Str.12',
        ];

        return array_combine($inputs, array_map(static fn(string $input): array => [$input], $inputs));
    }


    /**
     * @dataProvider spamProvider
     */
    public function testSpamIsBlockedAndTheFormIsCleared(string $input): void
    {
        $_POST['city'] = $input;

        $this->assertSame('', gprotector_block_all_urls_in_registration_form($input));
        $this->assertSame('', $_POST['firstname']);
        $this->assertSame('', $_POST['email_address']);
        $this->assertSame('', $_POST['city']);
    }


    public function spamProvider(): array
    {
        $inputs = [
            'spam.com',
            'tesst.com/x',
            'www.boese.de',
            'https://boese.de',
            'mein-shop.com',
            'blogspot.com/Viju',
            'billig.online',
            'test.store',
            'shop24.net',
            'sub.domain.co.uk',
            'xn--bcher-kva.de',
            'example[.]com',
            'example(dot)com',
            'example dot com',
        ];

        return array_combine($inputs, array_map(static fn(string $input): array => [$input], $inputs));
    }


    /**
     * The shop router only uses the leading word characters of each part of "do" and resolves action methods
     * case-insensitively, so all of these values end up in the same registration or guest action.
     *
     * @dataProvider registrationRouteProvider
     */
    public function testAppliesToEveryRouteThatReachesTheRegistration(string $do): void
    {
        $_GET['do'] = $do;

        $this->assertSame('', gprotector_block_all_urls_in_registration_form('spam.com'));
    }


    public function registrationRouteProvider(): array
    {
        $routes = [
            'CreateRegistree/Proceed',
            'CreateGuest/Proceed',
            'CreateRegistree/Proceed/x',
            'CreateGuest/Proceed.x',
            'CreateRegistree/proceed',
            'CreateGuest.x/Proceed',
        ];

        return array_combine($routes, array_map(static fn(string $route): array => [$route], $routes));
    }


    /**
     * The rule is registered for shop.php, which also serves other forms with the same field names, for example
     * the product question (whose spam trap field is called "lastname") and the parcel shop address.
     *
     * @dataProvider otherRouteProvider
     */
    public function testLeavesOtherRoutesAlone($do): void
    {
        if ($do === null) {
            unset($_GET['do']);
        } else {
            $_GET['do'] = $do;
        }

        $this->assertSame('spam.com', gprotector_block_all_urls_in_registration_form('spam.com'));
        $this->assertSame('Erika', $_POST['firstname']);
    }


    public function otherRouteProvider(): array
    {
        return [
            'no do parameter'       => [null],
            'empty do parameter'    => [''],
            'do parameter as array' => [['CreateRegistree/Proceed']],
            'registration form'     => ['CreateRegistree'],
            'product question'      => ['ProductQuestion/Send'],
            'parcel shop address'   => ['Parcelshopfinder/AddAddressBookEntry'],
        ];
    }


    /**
     * create_account.php and create_guest_account.php only redirect to shop.php, so the form is never posted to them.
     */
    public function testRuleOnlyTargetsTheScriptThatReceivesTheForm(): void
    {
        $rules = json_decode(file_get_contents(__DIR__ . '/../../filter/standard.json'), true);
        $rule  = current(array_filter($rules, static function (array $rule): bool {
            return $rule['function'] === 'block_all_urls_in_registration_form';
        }));

        $this->assertSame(['shop.php'], $rule['script_name']);
    }
}
