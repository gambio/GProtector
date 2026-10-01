<?php
/* --------------------------------------------------------------
  FilterCollection.php 2022-08-09
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2022 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace GProtector;

use InvalidArgumentException;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

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
    private function __construct(array $filters)
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
     * This function creates a new FilterCollection
     *
     * @param               $rawFilters
     * @param callable|null $onInvalidFilter called with ($rawFilter, InvalidArgumentException) for a rule that
     *                                       cannot be read; the rule is skipped. Without it, the exception is thrown.
     *
     * @return FilterCollection
     */
    
    public static function fromData($rawFilters, ?callable $onInvalidFilter = null)
    {
        $filterArray = [];
        foreach ($rawFilters as $rawFilter) {
            try {
                $filterArray[] = Filter::fromData($rawFilter);
            } catch (InvalidArgumentException $e) {
                if ($onInvalidFilter === null) {
                    throw $e;
                }
                $onInvalidFilter($rawFilter, $e);
            }
        }
    
        return new static($filterArray);
    }
    
    
    /**
     * @return ArrayIterator|Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->filterArray);
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
}