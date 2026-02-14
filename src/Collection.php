<?php
declare(strict_types=1);

namespace Amo\Collection;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template TKey of array-key
 * @template T
 * @template-extends ArrayCollection<TKey, T>
 *
 * @psalm-consistent-constructor
 */
class Collection extends ArrayCollection
{
    /**
     * Alias to static instantiation.
     *
     * @param array<TKey, T> $elements
     *
     * @return static<TKey, T>
     */
    public static function create(array $elements = []): Collection
    {
        return static::make($elements);
    }

    /**
     * Static instantiation,
     * useful for instantiation followed directly by chained syntax.
     *
     * @param array<TKey, T> $elements
     *
     * @return static<TKey, T>
     */
    public static function make(array $elements = [])
    {
        return new static($elements);
    }

    /**
     * Execute a callback over each item.
     * The closure return value is ignored.
     *
     * @param \Closure(T, TKey):mixed $callback
     *
     * @return $this
     */
    public function each(\Closure $callback): Collection
    {
        foreach ($this->toArray() as $key => $item) {
            $callback($item, $key);
        }

        return $this;
    }

    /**
     * Run a map over each of the elements.
     *
     * @template U
     *
     * @param \Closure(T, TKey):U $closure
     *
     * @return static<TKey, U>
     */
    public function map(\Closure $closure): Collection
    {
        $keys = $this->getKeys();
        $values = array_map($closure, $this->toArray(), $keys);

        return new static(array_combine($keys, $values));
    }

    /**
     * Run a map and flatten one level.
     *
     * @template U
     *
     * @param \Closure(T, TKey):iterable<array-key, U> $closure
     *
     * @return Collection<mixed, U>
     */
    public function flatMap(\Closure $closure): Collection
    {
        $flattened = static::make();
        foreach ($this->toArray() as $key => $item) {
            foreach ($closure($item, $key) as $value) {
                $flattened->add($value);
            }
        }

        return $flattened;
    }

    /**
     * Finds first element matching predicate.
     *
     * @param \Closure(T, TKey):bool $predicate
     *
     * @return T|null
     */
    public function find(\Closure $predicate): mixed
    {
        foreach ($this->toArray() as $key => $item) {
            if ($predicate($item, $key)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Group values by generated key.
     *
     * @param \Closure(T, TKey):array-key $groupBy
     *
     * @return Collection<array-key, Collection<mixed, T>>
     */
    public function groupBy(\Closure $groupBy): Collection
    {
        $grouped = [];
        foreach ($this->toArray() as $key => $item) {
            $groupKey = $groupBy($item, $key);
            if (!array_key_exists($groupKey, $grouped)) {
                $grouped[$groupKey] = static::make();
            }

            $grouped[$groupKey]->add($item);
        }

        return static::make($grouped);
    }

    /**
     * Re-index values by generated key.
     *
     * @param \Closure(T, TKey):array-key $keyBy
     *
     * @return Collection<array-key, T>
     */
    public function keyBy(\Closure $keyBy): Collection
    {
        $indexed = [];
        foreach ($this->toArray() as $key => $item) {
            $indexed[$keyBy($item, $key)] = $item;
        }

        return static::make($indexed);
    }

    /**
     * Take first N elements.
     *
     * @return static<mixed, T>
     */
    public function take(int $limit): Collection
    {
        return static::make(array_slice($this->toArray(), 0, $limit, true));
    }

    /**
     * Drop first N elements.
     *
     * @return static<mixed, T>
     */
    public function drop(int $limit): Collection
    {
        return static::make(array_slice($this->toArray(), $limit, null, true));
    }

    /**
     * Creates a new collection, containing elements from current and given collection
     * Keys of elements are not preserved.
     *
     * @param Collection<mixed, T> $collection
     *
     * @return static<mixed, T>
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
     * Append all elements of the given collection to current one.
     *
     * @param Collection<mixed, T> $collection
     *
     * @return $this
     */
    public function append(Collection $collection): Collection
    {
        $collection->each(function ($element) {
            $this->add($element);
        });

        return $this;
    }

    /**
     * Creates a copy of the current Collection.
     *
     * @return static<TKey, T>
     */
    public function copy(): Collection
    {
        return static::make($this->toArray());
    }

    /**
     * Sort the given collection with given closure and return a new collection.
     *
     * @param \Closure(T, T):int $sort A sorting method
     *
     * @return static<mixed, T>
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
     * Sort the collection with given closure and return a new collection.
     *
     * @param \Closure(T, T):int $sort
     *
     * @return static<mixed, T>
     */
    public function sort(\Closure $sort): Collection
    {
        return $this->usort($sort);
    }

    /**
     * Creates a flat collection from one level of nested arrays.
     *
     * @return Collection<mixed, mixed>
     */
    public function flatten(): Collection
    {
        $flattened = static::make();
        $this->each(function ($element) use ($flattened) {
            if (is_array($element)) {
                foreach ($element as $item) {
                    $flattened->add($item);
                }

                return;
            }

            $flattened->add($element);
        });

        return $flattened;
    }

    /**
     * Flip keys and values.
     * Values must be scalar because they become keys.
     *
     * @param \Closure|null $closure
     *
     * @return Collection<mixed, mixed>
     *
     * @throws \InvalidArgumentException
     */
    public function flip(?\Closure $closure = null): Collection
    {
        $flipped = static::make();
        foreach ($this->toArray() as $key => $value) {
            if (!is_scalar($value)) {
                throw new \InvalidArgumentException('Cannot flip an array containing non-scalar elements');
            }

            $flipped->set($value, $closure ? $closure($key, $value) : $key);
        }

        return $flipped;
    }

    /**
     * Create a unique collection.
     *
     * @param callable(T, T):bool|null $comparison
     *
     * @return static<mixed, T>
     */
    public function unique(?callable $comparison = null): Collection
    {
        $unique = static::make();
        $this->each(function ($element) use ($unique, $comparison) {
            if ($comparison) {
                foreach ($unique as $existing) {
                    if ($comparison($element, $existing)) {
                        return;
                    }
                }

                $unique->add($element);

                return;
            }

            if ($unique->contains($element)) {
                return;
            }

            $unique->add($element);
        });

        return $unique;
    }

    /**
     * Keep only elements present in the other collection.
     *
     * @param Collection<mixed, T> $other
     * @param callable(T, T):bool|null $comparison
     *
     * @return static<mixed, T>
     */
    public function intersect(Collection $other, ?callable $comparison = null): Collection
    {
        if ($comparison === null) {
            return static::make(array_values(array_filter(
                $this->toArray(),
                function ($current) use ($other) {
                    return $other->contains($current);
                }
            )));
        }

        $intersection = static::make();
        foreach ($this->toArray() as $current) {
            foreach ($other->toArray() as $candidate) {
                if ($comparison($current, $candidate)) {
                    $intersection->add($current);
                    continue 2;
                }
            }
        }

        return $intersection;
    }

    /**
     * Keep only elements absent from the other collection.
     *
     * @param Collection<mixed, T> $other
     * @param callable(T, T):bool|null $comparison
     *
     * @return static<mixed, T>
     */
    public function diff(Collection $other, ?callable $comparison = null): Collection
    {
        if ($comparison === null) {
            return static::make(array_values(array_filter(
                $this->toArray(),
                function ($current) use ($other) {
                    return !$other->contains($current);
                }
            )));
        }

        $difference = static::make();
        foreach ($this->toArray() as $current) {
            foreach ($other->toArray() as $candidate) {
                if ($comparison($current, $candidate)) {
                    continue 2;
                }
            }

            $difference->add($current);
        }

        return $difference;
    }

    /**
     * Reduces values to a new collection.
     *
     * @template U
     *
     * @param \Closure(array<array-key, U>, T):array<array-key, U> $callback
     * @param array<array-key, U> $initial
     *
     * @return Collection<array-key, U>
     *
     * @throws \InvalidArgumentException
     */
    public function reduce(\Closure $callback, mixed $initial = []): Collection
    {
        $reduced = array_reduce($this->getValues(), $callback, $initial);
        if (!is_array($reduced)) {
            throw new \InvalidArgumentException('Collection::reduce expects reducer to return an array result');
        }

        return static::make($reduced);
    }

    /**
     * Sums values from closure projection.
     *
     * @param \Closure(T):int|float $closure
     * @param int|float $initial
     *
     * @return int|float
     */
    public function sum(\Closure $closure, int|float $initial = 0): int|float
    {
        $sum = $initial;
        $this->each(function ($element) use ($closure, &$sum) {
            $sum += $closure($element);
        });

        return $sum;
    }

    /**
     * {@inheritDoc}
     *
     * @return static<mixed, T>
     */
    public function slice($offset, $length = null): Collection
    {
        return static::make(parent::slice($offset, $length));
    }
}
