<?php
/* --------------------------------------------------------------
  Filter.php 2020-02-24
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
     * @var string $key
     */
    private $key;
    
    /**
     * @var array $scriptNames
     */
    private $scriptNames;
    
    /**
     * @var array $variables
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
     * Initializes filter instance inside this class
     *
     * GProtectorFilter constructor.
     *
     * @param Key                  $key
     * @param ScriptNameCollection $scriptNames
     * @param VariableCollection   $variables
     * @param Method               $method
     * @param Severity             $severity
     */
    private function __construct(
        Key $key,
        ScriptNameCollection $scriptNames,
        VariableCollection $variables,
        Method $method,
        Severity $severity
    ) {
        $this->key         = $key->key();
        $this->scriptNames = $scriptNames->getArray();
        $this->variables   = $variables->getArray();
        $this->method      = $method->method();
        $this->severity    = $severity->severity();
    }
    
    
    /**
     * Getter for key
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }
    
    
    /**
     * Getter for script names
     *
     * @return array
     */
    public function scriptName()
    {
        return $this->scriptNames;
    }
    
    
    /**
     * Getter for variables
     *
     * @return array
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