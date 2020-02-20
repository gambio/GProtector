<?php
/* --------------------------------------------------------------
  Filter.php 2020-02-04
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
     * @var array $scriptName
     */
    private $scriptName;
    
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
     * @param Key                $key
     * @param ScriptName         $scriptName
     * @param VariableCollection $variables
     * @param Method             $method
     * @param Severity           $severity
     */
    private function __construct(
        Key $key,
        ScriptName $scriptName,
        VariableCollection $variables,
        Method $method,
        Severity $severity
    ) {
        $this->key        = $key->key();
        $this->scriptName = $scriptName->scriptName();
        $this->variables  = $variables->getArray();
        $this->method     = $method->method();
        $this->severity   = $severity->severity();
    }
    
    
    /**
     * @return string
     */
    public function key()
    {
        return $this->key;
    }
}