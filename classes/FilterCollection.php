<?php
/* --------------------------------------------------------------
  FilterCollection.php 2020-02-04
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use \InvalidArgumentException;
use \IteratorAggregate;
use \ArrayIterator;
use \Traversable;

class FilterCollection implements IteratorAggregate
{
    /**
     * @var array
     */
    private $filterArray = [];
    
    
    /**
     * Initializes the collection instance inside this class.
     *
     * @param array $filters
     *
     * @throws InvalidArgumentException
     *
     */
    public function __construct(array $filters)
    {
        foreach ($filters as $filter) {
            try {
                $this->add($filter);
            } catch (InvalidArgumentException $e) {
                throw $e;
            }
        }
    }
    
    
    /**
     * Add a new filter.
     *
     * @param Filter $filter Item which should be added to the collection
     *
     */
    private function add(Filter $filter)
    {
        $this->filterArray[] = $filter;
    }
    
    
    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->filterArray);
    }
}