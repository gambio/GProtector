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
use \UnexpectedValueException;

class Variable
{
    /**
     * @var String $type
     */
    private $type;
    
    /**
     * @var array $property
     */
    private $property = [];
    
    
    public function __construct($type, $property)
    {
        $this->validateType($type);
        $this->validateProperty($property);
        
        $this->type     = $type;
        $this->property = $property;
    }
    
    
    /**
     * Validates type
     *
     * @param mixed $type to be validated
     *
     * @throws InvalidArgumentException if type is null or not a string
     */
    private function validateType($type)
    {
        if ($type === null || (is_string($type)) === false) {
            throw new InvalidArgumentException('Invalid $type');
        }
    }
    
    
    /**
     * Validates property
     *
     * @param mixed $property to be validated
     *
     * @throws InvalidArgumentException, UnexpectedValueException
     *   if property is null or not an array or if property value is not a string or null
     *
     */
    private function validateProperty($property)
    {
        if ($property === null || (is_array($property)) === false) {
            throw new InvalidArgumentException('Invalid $property');
        }
        
        foreach ($property as $propertyValue) {
            
            if ($propertyValue === null || (is_string($propertyValue)) === false) {
                throw  new UnexpectedValueException('Invalid $propertyValue');
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
        return $this->property;
    }
}