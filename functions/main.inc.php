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
	$tFlags = ENT_COMPAT;
	if(defined('ENT_HTML401'))
	{
		$tFlags = ENT_COMPAT | ENT_HTML401;
	}
	
	$tEncoding = 'ISO-8859-15';
    if(preg_match('//u', $variable))
	{
		$tEncoding = 'UTF-8';
	}
	
	return htmlentities((string)$variable, $tFlags, $tEncoding);
}

function gprotectorHtmlspecialchars($variable)
{
	$tFlags = ENT_COMPAT;
	if(defined('ENT_HTML401'))
	{
		$tFlags = ENT_COMPAT | ENT_HTML401;
	}
	
	$tEncoding = 'ISO-8859-15';
    if(preg_match('//u', $variable))
	{
		$tEncoding = 'UTF-8';
	}
	
	return htmlspecialchars((string)$variable, $tFlags, $tEncoding);
}

function gprotectorFilterPrice($variable)
{
	$tPrice = trim((string)$variable);
	if(substr($tPrice, -1) == '%')
	{
		$tNumber = substr($tPrice, 0, -1);
		$tNumber = (double)$tNumber;
		$tPrice = $tNumber . '%';
	}
	else
	{
		$tPrice = (string)(double)$tPrice;
	}
	
	return $tPrice;
}

function gprotectorFilterText($variable)
{
	$tVariableArray = array();
	$cVariableArray = array();
	
	if(!is_array($variable))
	{
		$tVariableArray[] = $variable;
	}
	else
	{
		$tVariableArray = $variable;
	}
	
	$tForbiddenArray = array();
	$tForbiddenArray[] = 'onclick';
	$tForbiddenArray[] = 'ondblclick';
	$tForbiddenArray[] = 'onmousedown';
	$tForbiddenArray[] = 'onmousemove';
	$tForbiddenArray[] = 'onmouseover';
	$tForbiddenArray[] = 'onmouseout';
	$tForbiddenArray[] = 'onmouseup';
	$tForbiddenArray[] = 'onkeydown';
	$tForbiddenArray[] = 'onkeypress';
	$tForbiddenArray[] = 'onkeyup';
	$tForbiddenArray[] = 'onabort';
	$tForbiddenArray[] = 'onerror';
	$tForbiddenArray[] = 'onload';
	$tForbiddenArray[] = 'onresize';
	$tForbiddenArray[] = 'onscroll';
	$tForbiddenArray[] = 'onunload';
	$tForbiddenArray[] = 'onblur';
	$tForbiddenArray[] = 'onchange';
	$tForbiddenArray[] = 'onfocus';
	$tForbiddenArray[] = 'onreset';
	$tForbiddenArray[] = 'onselect';
	$tForbiddenArray[] = 'onsubmit';
	$tForbiddenArray[] = 'src';
	$tForbiddenArray[] = '"';
	$tForbiddenArray[] = '<';
	
	$tPatternArray = array();
	$tPatternArray[] = '/onclick\s*=/i';
	$tPatternArray[] = '/ondblclick\s*=/i';
	$tPatternArray[] = '/onmousedown\s*=/i';
	$tPatternArray[] = '/onmousemove\s*=/i';
	$tPatternArray[] = '/onmouseover\s*=/i';
	$tPatternArray[] = '/onmouseout\s*=/i';
	$tPatternArray[] = '/onmouseup\s*=/i';
	$tPatternArray[] = '/onkeydown\s*=/i';
	$tPatternArray[] = '/onkeypress\s*=/i';
	$tPatternArray[] = '/onkeyup\s*=/i';
	$tPatternArray[] = '/onabort\s*=/i';
	$tPatternArray[] = '/onerror\s*=/i';
	$tPatternArray[] = '/onload\s*=/i';
	$tPatternArray[] = '/onresize\s*=/i';
	$tPatternArray[] = '/onscroll\s*=/i';
	$tPatternArray[] = '/onunload\s*=/i';
	$tPatternArray[] = '/onblur\s*=/i';
	$tPatternArray[] = '/onchange\s*=/i';
	$tPatternArray[] = '/onfocus\s*=/i';
	$tPatternArray[] = '/onreset\s*=/i';
	$tPatternArray[] = '/onselect\s*=/i';
	$tPatternArray[] = '/onsubmit\s*=/i';
	$tPatternArray[] = '/src\s*=/i';
	$tPatternArray[] = '/".*</';
	
	foreach($tVariableArray as $tKey => $tValue)
	{
		$cValue = (string)$tValue;
		$cVariableArray[$tKey] = $cValue;
		
		foreach($tPatternArray as $tPattern)
		{
			if(preg_match($tPattern, $cValue))
			{
				$cValue = str_replace($tForbiddenArray, '', $cValue);
				$tSearchArray = array('&', '"', "'", '<', '>');
				$tReplaceArray = array('&amp;', '&quot;', "&#039;", '&lt;', '&gt;');
				$cVariableArray[$tKey] = str_replace($tSearchArray, $tReplaceArray, str_replace($tSearchArray, $tReplaceArray, $cValue));
			}
		}
	}
	
	if(!is_array($variable))
	{
		$tReturnVariable = array_pop($cVariableArray);
	}
	else
	{
		$tReturnVariable = $cVariableArray;
	}
	
	return $tReturnVariable;
}

function gprotector_only_numeric($variable)
{
	return preg_replace('/[^0-9.,-]/', '', (string)$variable);
}

function gprotector_only_hex_code($pVariable)
{
	return preg_replace('/[^a-fA-F0-9#]/', '', (string)$pVariable);
}

function gprotectorRecursiveIntegerValue($variable)
{
	if(is_array($variable))
	{
		$cVariable = array();
		
		foreach($variable as $tKey => $tValue)
		{
			$cVariable[$tKey] = '';
			if($tValue !== '')
			{
				$cVariable[$tKey] = gprotectorRecursiveIntegerValue($tValue);
			}
		}
	}
	else
	{
		$cVariable = gprotectorConvertToInteger($variable);
	}
	
	return $cVariable;
}


function gprotectorRecursiveOnlyAlphanumeric($variable)
{
	if(is_array($variable))
	{
		$cVariable = array();
		
		foreach($variable as $tKey => $tValue)
		{
			$cVariable[$tKey] = gprotectorRecursiveOnlyAlphanumeric($tValue);
		}
	}
	else
	{
		$cVariable = gprotectorOnlyAlphanumeric($variable);
	}
	
	return $cVariable;
}


function gprotectorRecursiveFilterText($variable)
{
	if(is_array($variable))
	{
		$cVariable = array();
		
		foreach($variable as $tKey => $tValue)
		{
			$cVariable[$tKey] = gprotectorRecursiveFilterText($tValue);
		}
	}
	else
	{
		$cVariable = gprotectorFilterText($variable);
	}
	
	return $cVariable;
}


function gprotectorRecursiveOnlySafeCharacters($variable)
{
	if(is_array($variable))
	{
		$cVariable = array();
		
		foreach($variable as $tKey => $tValue)
		{
			$cVariable[$tKey] = gprotectorRecursiveOnlySafeCharacters($tValue);
		}
	}
	else
	{
		$cVariable = gprotectorOnlySafeCharacters($variable);
	}
	
	return $cVariable;
}


function gprotectorRecursiveHtmlspecialchars($variable)
{
	if(is_array($variable))
	{
		$cVariable = array();
		
		foreach($variable as $tKey => $tValue)
		{
			$cVariable[$tKey] = gprotectorRecursiveHtmlspecialchars($tValue);
		}
	}
	else
	{
		$cVariable = gprotectorHtmlspecialchars($variable);
	}
	
	return $cVariable;
}


function gprotectorBasename($variable)
{
	$cVariable = '';
	
	if(is_string($variable))
	{
		$cVariable = basename($variable);
	}
	
	return $cVariable;
}


function gprotectorFilterTags($variable)
{
	$cVariable = '';
	
	if(is_string($variable))
	{
		$cVariable = str_replace(array('<', '>'), '', $variable);
	}
	
	return $cVariable;
}


function gprotectorStripTags($variable)
{
	$cVariable = '';
	
	if(is_string($variable))
	{
		$cVariable = strip_tags($variable);
	}
	
	return $cVariable;
}


function gprotectorRecursiveFilterTags($variable)
{
	if(is_array($variable))
	{
		$cVariable = array();
		
		foreach($variable as $tKey => $tValue)
		{
			$cVariable[$tKey] = gprotectorRecursiveFilterTags($tValue);
		}
	}
	else
	{
		$cVariable = str_replace(array('<', '>'), '', $variable);
	}
	
	return $cVariable;
}


function gprotectorBlockAllUrlsInRegistrationForm($variable)
{
	if((!isset($_GET['do']) || $_GET['do'] === 'CreateRegistree/Proceed' || $_GET['do'] === 'CreateGuest/Proceed')
	   && (strpos($variable, 'http://') !== false || strpos($variable, 'https://') !== false))
	{
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
	if(is_array($variable))
	{
		$cVariable = array();

		foreach($variable as $tKey => $tValue)
		{
			$cKey = gprotectorConvertToInteger($tKey);
			$cVariable[$cKey] = '';
			if($tValue !== '')
			{
				$cVariable[$cKey] = gprotectorRecursiveIntegerValue($tValue);
			}
		}
	}
	else
	{
		$cVariable = preg_replace('/[^0-9&:\|]/', '', (string)$variable);
	}
	
	return $cVariable;
}
