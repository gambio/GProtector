<?php
/* --------------------------------------------------------------
  Filter.php 2020-07-31
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
     * @var string|null $method
     */
    private $method;
    
    /**
     * @var string $severity
     */
    private $severity;
    
    /**
     * @var string $action
     */
    private $action;
    
    /**
     * @var Pattern|null $pattern
     */
    private $pattern;
    
    
    /**
     * Initializes filter instance inside this class
     *
     * GProtectorFilter constructor.
     *
     * @param Key                  $key
     * @param ScriptNameCollection $scriptNames
     * @param VariableCollection   $variables
     * @param Method|null          $method
     * @param Severity             $severity
     * @param Action               $action
     * @param Pattern|null         $pattern
     */
    private function __construct(
        Key $key,
        ScriptNameCollection $scriptNames,
        VariableCollection $variables,
        ?Method $method,
        Severity $severity,
        Action $action,
        ?Pattern $pattern
    ) {
        $this->key         = $key->key();
        $this->scriptNames = $scriptNames->getArray();
        $this->variables   = $variables->getArray();
        $this->method      = $method === null ? null : $method->method();
        $this->severity    = $severity->severity();
        $this->action      = $action->action();
        $this->pattern     = $pattern;
    }
    
    
    /**
     * This function creates new Filter objects
     *
     * @param $rawFilter
     *
     * @return Filter
     * @throws InvalidArgumentException
     */
    
    public static function fromData($rawFilter)
    {
        $key = new Key($rawFilter['key']);
    
        $scriptNames = [];
        if (is_array($rawFilter['script_name'])) {
            foreach ($rawFilter['script_name'] as $scriptName) {
                $scriptNames[] = new ScriptName($scriptName);
            }
        } else {
            $scriptNames[] = new ScriptName($rawFilter['script_name']);
        }
        $scriptNameCollection = new ScriptNameCollection($scriptNames);
    
        $variables = [];
        foreach ($rawFilter['variables'] as $variableName) {
            $isSubcategory = isset($variableName['subcategory']) && is_array($variableName['property']);
            $variables[] = new Variable($variableName['type'], $variableName['property'], $isSubcategory ? $variableName['subcategory'] : null);
        }
        $variableCollection = new VariableCollection($variables);
        $severity           = new Severity($rawFilter['severity']);
        $action             = new Action($rawFilter['action'] ?? Action::SANITIZE);
        $pattern            = isset($rawFilter['pattern']) ? new Pattern($rawFilter['pattern']) : null;
        $isDeny             = $action->action() === Action::DENY;
        
        if ($isDeny && $pattern === null) {
            throw new InvalidArgumentException('A deny rule needs a $pattern');
        }
        if ($isDeny && $pattern->matches('')) {
            throw new InvalidArgumentException('The $pattern of a deny rule must not match an empty value');
        }
        
        // a deny rule may omit the function; older engines still need one to accept the file
        $method = !$isDeny || isset($rawFilter['function']) ? new Method($rawFilter['function'] ?? null) : null;
        
        return new static($key, $scriptNameCollection, $variableCollection, $method, $severity, $action, $pattern);
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
     * @return string|null
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
    
    
    /**
     * Getter for action
     *
     * @return string
     */
    public function action()
    {
        return $this->action;
    }
    
    
    /**
     * Getter for pattern
     *
     * @return Pattern|null
     */
    public function pattern()
    {
        return $this->pattern;
    }
}