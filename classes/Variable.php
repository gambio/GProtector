<?php

/* --------------------------------------------------------------
  Variable.php 2020-02-26
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use \InvalidArgumentException;

class Variable
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
     * @param string $type
     * @param array  $properties
     */
    private function __construct($type, $properties)
    {
        $this->validateType($type);
        $this->validateProperties($properties);
        
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
        $typeArray = ['POST', 'GET'];
        if (in_array(strtoupper($type), $typeArray) === false) {
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
     */
    private function validateProperties($properties)
    {
        if ($properties === null || (is_array($properties)) === false) {
            throw new InvalidArgumentException('Invalid $properties');
        }
        
        foreach ($properties as $property) {
            if ($property === null || (is_string($property)) === false) {
                throw new InvalidArgumentException('Invalid $property');
            }
        }
    }
    
}