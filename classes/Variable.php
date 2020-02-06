<?php

namespace GProtector;

class Variable
{
  private $type;
  private $property = [];
    
    
    public function __construct($type, $property)
    {
    
  }
    
    private function validateType($type)
    {
        if ($type === null || (is_string($type)) === false) {
            throw new InvalidArgumentException('Invalid $type \'' . $type . '\'');
        }
    }
    
    private function validateProperty($property)
    {
        if ($property === null || (is_array($property)) === false) {
            throw new InvalidArgumentException('Invalid $property \'' . $property . '\'');
        }
        
        foreach ($property as $propertyValue){
          
            if ($propertyValue === null ||(is_string($propertyValue ))=== false){
              throw  new \UnexpectedValueException('Invalid $propertyValue \'' . $propertyValue . '\'');
            }
    }
    }
    
    
    /**
     * @return mixed
     */
    public function type()
    {
        return $this->type;
    }
    
    
    /**
     * @return array
     */
    public function property()
    {
        return $this->property;
    }
}