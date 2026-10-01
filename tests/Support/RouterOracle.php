<?php

namespace GProtector\Tests\Support;

/**
 * The shop's own rule for turning `do` into a controller name. Ground truth for deny-pattern tests.
 *
 * Mirrors HttpContextReader::getControllerName() in the Gambio shop
 * (src/GXEngine/Services/System/Http/HttpContextReader.inc.php). Keep in sync if the router changes.
 */
final class RouterOracle
{
    public static function controllerName(string $do): string
    {
        return preg_split('/\W/', explode('/', $do)[0])[0];
    }
}
