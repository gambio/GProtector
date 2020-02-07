<?php

/* --------------------------------------------------------------
  GProtectorFilter.php 2020-02-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use \InvalidArgumentException;

class ScriptName
{
    
    /**
     * @var array $scriptName
     */
    private $scriptName;
    
    
    /**
     * Initializes scriptName instance inside this class
     *
     * ScriptName constructor.
     *
     * @param $scriptName
     */
    private function __construct($scriptName)
    {
        $this->validateScriptName($scriptName);
        $this->scriptName = $scriptName;
    }
    
    
    /**
     * Validates a scriptname
     *
     * @param mixed $scriptName The scriptname to validate
     *
     * @throws InvalidArgumentException If the script name is null or not an array
     *
     */
    private function validateScriptName($scriptName)
    {
        if ($scriptName === null || (is_array($scriptName)) === false) {
            throw new InvalidArgumentException('Invalid $scriptName');
        }
    }
    
    
    /**
     * Getter for scriptname
     *
     * @return array
     */
    public function scriptName()
    {
        return $this->scriptName;
    }
    
    
}