<?php

/* --------------------------------------------------------------
  Variable.php 2020-02-21
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
     * @var string $type
     */
    private $type;
    
    /**
     * @var array $properties
     */
    private $properties = [];
    
    
    /**
     * Initializes variable instance
     *
     * Variable constructor.
     *
     * @param $type
     * @param $properties
     */
    private function __construct($type, $properties)
    {
        $this->validateType($type);
        $this->validateProperty($properties);
        
        $this->type       = $type;
        $this->properties = $properties;
    }
    
    
    /**
     * Validates type
     *
     * @param mixed $type to be validated
     *
     * @throws InvalidArgumentException if type is null or not equals to post or get
     */
    private function validateType($type)
    {
        if ($type === null || (strtolower($type)) !== 'post' || (strtolower($type)) !== 'get') {
            throw new InvalidArgumentException('Invalid $type');
        }
    }
    
    
    /**
     * Validates property
     *
     * @param mixed $properties to be validated
     *
     * @throws InvalidArgumentException if property is null or not an array
     *
     * @throws  InvalidArgumentException if property is null or not a string
     *
     */
    private function validateProperty($properties)
    {
        if ($properties === null || (is_array($properties)) === false) {
            throw new InvalidArgumentException('Invalid $property');
        }
        
        foreach ($properties as $property) {
            
            if ($property === null || (is_string($property)) === false) {
                throw  new InvalidArgumentException('Invalid $property');
            }
        }
    }
    
    
    /**
     * Getter for type
     *
     * @return String
     */
    public function type()
    {
        return $this->type;
    }
    
    
    /**
     * Getter for property
     *
     * @return array
     */
    public function property()
    {
        return $this->properties;
    }
}