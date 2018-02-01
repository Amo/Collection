<?php
declare(strict_types=1);

namespace Amo\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class Collection extends ArrayCollection
{
    /**
     * @param array $elements
     *
     * @return Collection
     */
    public static function make(array $elements = [])
    {
        return new static($elements);
    }

    /**
     * Execute a callback over each item.
     * Only stops if the closure returns explicitly false
     * Unlike ArrayCollection::forAll that interrupts if the closure
     * returns anything evaluated to false
     *
     * @param  \Closure $callback
     *
     * @return $this
     */
    public function each(\Closure $callback): Collection
    {
        foreach ($this->toArray() as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Run a map over each of the elements.
     *
     * @param \Closure $closure
     *
     * @return static
     */
    public function map(\Closure $closure): Collection
    {
        $keys = $this->getKeys();
        $values = array_map($closure, $this->toArray(), $keys);

        return new static(array_combine($keys, $values));
    }

    /**
     * Creates a new collection, containing elements from current and given collection
     * Keys of elements are not preserved.
     *
     * @param  Collection $collection
     *
     * @return static
     */
    public function merge(Collection $collection): Collection
    {
        $newCollection = static::make($collection->toArray());
        $this->each(function ($element) use ($newCollection) {
            $newCollection->add($element);
        });

        return $newCollection;
    }

    /**
     * Creates a copy of the currrent Collection
     *
     * @return Collection
     */
    public function copy(): Collection
    {
        return static::make($this->toArray());
    }

    /**
     * Sort the given collection with given closure and return a new collection.
     *
     * @param \Closure $sort A sorting method
     *
     * @return Collection
     *
     * @throws \RuntimeException
     */
    public function usort(\Closure $sort): Collection
    {
        $items = $this->toArray();
        if (usort($items, $sort)) {
            return static::make($items);
        }

        // @codeCoverageIgnoreStart
        // Only happens when $items is not an array or PHP internal error
        // That really tricky to test
        throw new \RuntimeException('Failed to sort the collection with given sorting function');
        // @codeCoverageIgnoreEnd

    }

    /**
     * {@inheritDoc}
     *
     * @return Collection
     */
    public function slice($offset, $length = null)
    {
        return static::make(parent::slice($offset, $length));
    }
}