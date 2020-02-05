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
     * @var VariableCollection $variables
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
        $this->validateVariables($variables);
        $this->validateFunction($function);
        $this->validateSeverity($severity);
        
        $this->key        = $key;
        $this->scriptName = $scriptName;
        $this->variables  = $variables;
        $this->function   = $function;
        $this->severity   = $severity;
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
     * Validates a scriptname
     *
     * @param mixed $scriptName The scriptname to validate
     */
    private function validateScriptName($scriptName)
    {
        if ($scriptName === null) {
            throw new InvalidArgumentException('Invalid $key \'' . $scriptName . '\'');
        }
    }
    
    
    /**
     * Validates the variables
     *
     * @param mixed $variables The variables to validate
     *
     */
    private function validateVariables($variables)
    {
        
        if ($variables === null) {
            throw new InvalidArgumentException('Invalid $variables \'' . $variables . '\'');
        }
    }
    
    
    /**
     * Validates a function
     *
     * @param mixed $function The function to validate
     *
     */
    private function validateFunction($function)
    {
        if ($function === null) {
            throw new InvalidArgumentException('Invalid $function \'' . $function . '\'');
        }
    }
    
    
    /**
     * Validates a severity level
     *
     * @param mixed $severity The severity to validate
     *
     */
    private function validateSeverity($severity)
    {
        if ($severity === null) {
            throw new InvalidArgumentException('Invalid $severity \'' . $severity . '\'');
        }
    }
    
    
    /**
     * Getter for key
     *
     * @return string
     */
    private function key()
    {
        return $this->key;
    }
    
    
    /**
     * Getter for scriptname
     *
     * @return array
     */
    private function scriptName()
    {
        return $this->scriptName;
    }
    
    
    /**
     * Getter for variables
     *
     * @return VariableCollection
     */
    private function variables()
    {
        return $this->variables;
    }
    
    
    /**
     * Getter for function
     *
     * @return string
     */
    private function function ()
    {
        return $this->function;
    }
    
    
    /**
     * Getter for severity
     *
     * @return string
     */
    private function severity()
    {
        return $this->severity;
    }
    
}