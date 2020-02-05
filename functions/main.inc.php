<?php
/* --------------------------------------------------------------
  main.inc.php 2019-06-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

function gprotectorConvertToInteger($variable)
{
    return (string)(int)$variable;
}

function gprotectorOnlyAlphabetic($variable)
{
    return preg_replace('/[^a-zA-Z]/', '', (string)$variable);
}

function gprotectorOnlyAlphanumeric($variable)
{
    return preg_replace('/[^a-zA-Z0-9\s_-]/', '', (string)$variable);
}

function gprotectorOnlySafeCharacters($variable)
{
    return preg_replace('/[^a-zA-Z0-9_.,*\s@-]/', '', (string)$variable);
}

function gprotectorHtmlentities($variable)
{
    $flags = ENT_COMPAT;
    if (defined('ENT_HTML401')) {
        $flags = ENT_COMPAT | ENT_HTML401;
    }
    
    $encoding = 'ISO-8859-15';
    if (preg_match('//u', $variable)) {
        $encoding = 'UTF-8';
    }
    
    return htmlentities((string)$variable, $flags, $encoding);
}

function gprotectorHtmlspecialchars($variable)
{
    $flags = ENT_COMPAT;
    if (defined('ENT_HTML401')) {
        $flags = ENT_COMPAT | ENT_HTML401;
    }
    
    $encoding = 'ISO-8859-15';
    if (preg_match('//u', $variable)) {
        $encoding = 'UTF-8';
    }
    
    return htmlspecialchars((string)$variable, $flags, $encoding);
}

function gprotectorFilterPrice($variable)
{
    $price = trim((string)$variable);
    if (substr($price, -1) == '%') {
        $number = substr($price, 0, -1);
        $number = (double)$number;
        $price  = $number . '%';
    } else {
        $price = (string)(double)$price;
    }
    
    return $price;
}

function gprotectorFilterText($variable)
{
    $variableArray       = [];
    $stringVariableArray = [];
    
    if (!is_array($variable)) {
        $variableArray[] = $variable;
    } else {
        $variableArray = $variable;
    }
    
    $forbiddenArray   = [];
    $forbiddenArray[] = 'onclick';
    $forbiddenArray[] = 'ondblclick';
    $forbiddenArray[] = 'onmousedown';
    $forbiddenArray[] = 'onmousemove';
    $forbiddenArray[] = 'onmouseover';
    $forbiddenArray[] = 'onmouseout';
    $forbiddenArray[] = 'onmouseup';
    $forbiddenArray[] = 'onkeydown';
    $forbiddenArray[] = 'onkeypress';
    $forbiddenArray[] = 'onkeyup';
    $forbiddenArray[] = 'onabort';
    $forbiddenArray[] = 'onerror';
    $forbiddenArray[] = 'onload';
    $forbiddenArray[] = 'onresize';
    $forbiddenArray[] = 'onscroll';
    $forbiddenArray[] = 'onunload';
    $forbiddenArray[] = 'onblur';
    $forbiddenArray[] = 'onchange';
    $forbiddenArray[] = 'onfocus';
    $forbiddenArray[] = 'onreset';
    $forbiddenArray[] = 'onselect';
    $forbiddenArray[] = 'onsubmit';
    $forbiddenArray[] = 'src';
    $forbiddenArray[] = '"';
    $forbiddenArray[] = '<';
    
    $patternArray   = [];
    $patternArray[] = '/onclick\s*=/i';
    $patternArray[] = '/ondblclick\s*=/i';
    $patternArray[] = '/onmousedown\s*=/i';
    $patternArray[] = '/onmousemove\s*=/i';
    $patternArray[] = '/onmouseover\s*=/i';
    $patternArray[] = '/onmouseout\s*=/i';
    $patternArray[] = '/onmouseup\s*=/i';
    $patternArray[] = '/onkeydown\s*=/i';
    $patternArray[] = '/onkeypress\s*=/i';
    $patternArray[] = '/onkeyup\s*=/i';
    $patternArray[] = '/onabort\s*=/i';
    $patternArray[] = '/onerror\s*=/i';
    $patternArray[] = '/onload\s*=/i';
    $patternArray[] = '/onresize\s*=/i';
    $patternArray[] = '/onscroll\s*=/i';
    $patternArray[] = '/onunload\s*=/i';
    $patternArray[] = '/onblur\s*=/i';
    $patternArray[] = '/onchange\s*=/i';
    $patternArray[] = '/onfocus\s*=/i';
    $patternArray[] = '/onreset\s*=/i';
    $patternArray[] = '/onselect\s*=/i';
    $patternArray[] = '/onsubmit\s*=/i';
    $patternArray[] = '/src\s*=/i';
    $patternArray[] = '/".*</';
    
    foreach ($variableArray as $key => $value) {
        $stringValue               = (string)$value;
        $stringVariableArray[$key] = $stringValue;
        
        foreach ($patternArray as $pattern) {
            if (preg_match($pattern, $stringValue)) {
                $stringValue               = str_replace($forbiddenArray, '', $stringValue);
                $searchArray               = ['&', '"', "'", '<', '>'];
                $replaceArray              = ['&amp;', '&quot;', "&#039;", '&lt;', '&gt;'];
                $stringVariableArray[$key] = str_replace(
                    $searchArray,
                    $replaceArray,
                    str_replace($searchArray, $replaceArray, $stringValue)
                );
            }
        }
    }
    
    if (!is_array($variable)) {
        $returnVariable = array_pop($stringVariableArray);
    } else {
        $returnVariable = $stringVariableArray;
    }
    
    return $returnVariable;
}

function gprotector_only_numeric($variable)
{
    return preg_replace('/[^0-9.,-]/', '', (string)$variable);
}

function gprotector_only_hex_code($variable)
{
    return preg_replace('/[^a-fA-F0-9#]/', '', (string)$variable);
}

function gprotectorRecursiveIntegerValue($variable)
{
    if (is_array($variable)) {
        $arrayVariable = [];
        
        foreach ($variable as $key => $value) {
            $arrayVariable[$key] = '';
            if ($value !== '') {
                $arrayVariable[$key] = gprotectorRecursiveIntegerValue($value);
            }
        }
    } else {
        $arrayVariable = gprotectorConvertToInteger($variable);
    }
    
    return $arrayVariable;
}

function gprotectorRecursiveOnlyAlphanumeric($variable)
{
    if (is_array($variable)) {
        $arrayVariable = [];
        
        foreach ($variable as $key => $value) {
            $arrayVariable[$key] = gprotectorRecursiveOnlyAlphanumeric($value);
        }
    } else {
        $arrayVariable = gprotectorOnlyAlphanumeric($variable);
    }
    
    return $arrayVariable;
}

function gprotectorRecursiveFilterText($variable)
{
    if (is_array($variable)) {
        $arrayVariable = [];
        
        foreach ($variable as $key => $value) {
            $arrayVariable[$key] = gprotectorRecursiveFilterText($value);
        }
    } else {
        $arrayVariable = gprotectorFilterText($variable);
    }
    
    return $arrayVariable;
}

function gprotectorRecursiveOnlySafeCharacters($variable)
{
    if (is_array($variable)) {
        $arrayVariable = [];
        
        foreach ($variable as $key => $value) {
            $arrayVariable[$key] = gprotectorRecursiveOnlySafeCharacters($value);
        }
    } else {
        $arrayVariable = gprotectorOnlySafeCharacters($variable);
    }
    
    return $arrayVariable;
}

function gprotectorRecursiveHtmlspecialchars($variable)
{
    if (is_array($variable)) {
        $arrayVariable = [];
        
        foreach ($variable as $key => $value) {
            $arrayVariable[$key] = gprotectorRecursiveHtmlspecialchars($value);
        }
    } else {
        $arrayVariable = gprotectorHtmlspecialchars($variable);
    }
    
    return $arrayVariable;
}

function gprotectorBasename($variable)
{
    $stringVariable = '';
    
    if (is_string($variable)) {
        $stringVariable = basename($variable);
    }
    
    return $stringVariable;
}

function gprotectorFilterTags($variable)
{
    $stringVariable = '';
    
    if (is_string($variable)) {
        $stringVariable = str_replace(['<', '>'], '', $variable);
    }
    
    return $stringVariable;
}

function gprotectorStripTags($variable)
{
    $stringVariable = '';
    
    if (is_string($variable)) {
        $stringVariable = strip_tags($variable);
    }
    
    return $stringVariable;
}

function gprotectorRecursiveFilterTags($variable)
{
    if (is_array($variable)) {
        $arrayVariable = [];
        
        foreach ($variable as $key => $value) {
            $arrayVariable[$key] = gprotectorRecursiveFilterTags($value);
        }
    } else {
        $arrayVariable = str_replace(['<', '>'], '', $variable);
    }
    
    return $arrayVariable;
}

function gprotectorBlockAllUrlsInRegistrationForm($variable)
{
    if ((!isset($_GET['do']) || $_GET['do'] === 'CreateRegistree/Proceed' || $_GET['do'] === 'CreateGuest/Proceed')
        && (strpos($variable, 'http://') !== false || strpos($variable, 'https://') !== false)) {
        $_POST["firstname"]               = '';
        $_POST["lastname"]                = '';
        $_POST["email_address"]           = '';
        $_POST["email_address_confirm"]   = '';
        $_POST["vat"]                     = '';
        $_POST["street_address"]          = '';
        $_POST["house_number"]            = '';
        $_POST["additional_address_info"] = '';
        $_POST["suburb"]                  = '';
        $_POST["postcode"]                = '';
        $_POST["city"]                    = '';
        $_POST["state"]                   = '';
        $_POST["country"]                 = '';
        $_POST["telephone"]               = '';
        $_POST["fax"]                     = '';
        
        return '';
    }
    
    return $variable;
}

function gprotectorFilterIds($variable)
{
    if (is_array($variable)) {
        $arrayVariable = [];
        
        foreach ($variable as $key => $value) {
            $intKey                 = gprotectorConvertToInteger($key);
            $arrayVariable[$intKey] = '';
            if ($value !== '') {
                $arrayVariable[$intKey] = gprotectorRecursiveIntegerValue($value);
            }
        }
    } else {
        $arrayVariable = preg_replace('/[^0-9&:\|]/', '', (string)$variable);
    }
    
    return $arrayVariable;
}
