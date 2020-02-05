<?php
/* --------------------------------------------------------------
  GProtectorFilter.php 2020-02-04
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

class Filter
{
    /**
     * @var  string $key
     */
    private $key;
    
    /**
     * @var array $scriptName
     */
    private $scriptName;
    
    /**
     * @var array $variables
     */
    private $variables;
    
    /**
     * @var string $function
     */
    private $function;
    
    /**
     * @var string $severity
     */
    private $severity;
    
    
    /**
     * GProtectorFilter constructor.
     *
     * @param mixed              $key
     * @param mixed              $scriptName
     * @param VariableCollection $variables
     * @param mixed              $function
     * @param mixed              $severity
     */
    public function __construct($key, $scriptName, VariableCollection $variables, $function, $severity)
    {
        $this->validateKey($key);
        $this->validateScriptName($scriptName);
        
        $this->key        = $key;
        $this->scriptName = $scriptName;
        
        if ($this->validateKey($key) === true) {
            $this->key = $key;
        }
        if ($this->validateScriptName($scriptName) === true) {
            $this->scriptName = $scriptName;
        }
        if ($this->validateVariables($variables) === true) {
            $this->variables = $variables;
        }
        if ($this->validateFunction($function) === true) {
            $this->function = $function;
        }
        if ($this->validateSeverity($severity) === true) {
            $this->severity = $severity;
        }
    }
    
    
    /**
     * Validates a key.
     *
     * @param mixed $key The key to validate
     */
    private function validateKey($key)
    {
        if ($key === null) {
            throw new InvalidArgumentException('Invalid $key \'' . $key . '\'');
        }
    }
    
    
    /**
     * @param mixed $scriptName
     *
     * @return boolean
     */
    private function validateScriptName($scriptName)
    {
        if (is_array($scriptName)) {
            return true;
        }
    }
    
    
    /**
     * @param $variables
     *
     * @return boolean
     */
    private function validateVariables($variables)
    {
        
        if (is_array($variables)) {
            return true;
        }
    }
    
    
    /**
     * @param $function
     *
     * @return boolean
     */
    private function validateFunction($function)
    {
        if ($function !== null) {
            return true;
        }
    }
    
    
    /**
     * @param $severity
     *
     * @return boolean
     */
    private function validateSeverity($severity)
    {
        if ($severity !== null) {
            return true;
        }
    }
    
    
    private function key()
    {
    
    }
}