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

use \InvalidArgumentException;

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
     * @var string $method
     */
    private $method;
    
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
     * @param mixed              $method
     * @param mixed              $severity
     */
    public function __construct(Key $key, $scriptName, VariableCollection $variables, $method, $severity)
    {
        $this->validateScriptName($scriptName);
        $this->validateVariables($variables);
        $this->validateMethod($method);
        $this->validateSeverity($severity);
        
        $this->key        = $key->key();
        $this->scriptName = $scriptName;
        $this->variables  = $variables;
        $this->method     = $method;
        $this->severity   = $severity;
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
     * Validates the variables
     *
     * @param mixed $variables The variables to validate
     *
     * @throws InvalidArgumentException
     *
     */
    private function validateVariables($variables)
    {
        if ($variables === null) {
            throw new InvalidArgumentException('$variables must not be null');
        }
    }
    
    
    /**
     * Validates a method
     *
     * @param mixed $method The method to validate
     *
     * @throws InvalidArgumentException
     *
     */
    private function validateMethod($method)
    {
        if ($method === null || (is_string($method)) === false) {
            throw new InvalidArgumentException('Invalid $method \'' . $method . '\'');
        }
    }
    
    
    /**
     * Validates a severity level
     *
     * @param mixed $severity The severity to validate
     *
     * @throws InvalidArgumentException
     *
     */
    private function validateSeverity($severity)
    {
        if ($severity === null || (is_string($severity)) === false) {
            throw new InvalidArgumentException('Invalid $severity \'' . $severity . '\'');
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
    
    
    /**
     * Getter for variables
     *
     * @return VariableCollection
     */
    public function variables()
    {
        return $this->variables;
    }
    
    
    /**
     * Getter for method
     *
     * @return string
     */
    public function method()
    {
        return $this->method;
    }
    
    
    /**
     * Getter for severity
     *
     * @return string
     */
    public function severity()
    {
        return $this->severity;
    }
    
}