# Collection
Collection management, heavily inspired by Doctrine ArrayCollection

# Install

```
composer require amo/collection
```

# Docker (latest PHP)

```
docker compose build
docker compose run --rm php composer install
docker compose run --rm php vendor/bin/phpunit -c phpunit.xml
```

# Methods: Usage + Outcome

```php
use Amo\Collection\Collection;
```

`make` / `create`
- `Collection::make([1, 2, 3])` -> collection with values `[1, 2, 3]`
- `Collection::create(['a' => 10])` -> collection with key/value `['a' => 10]`

`each` (return value ignored, iterates all)
- `Collection::make([1, 2, 3])->each(fn(int $v) => print($v));` -> prints `123`

`map`
- `Collection::make([1, 2])->map(fn(int $v) => $v * 10)->getValues()` -> `[10, 20]`

`flatMap`
- `Collection::make([[1, 2], [3]])->flatMap(fn(array $x) => $x)->getValues()` -> `[1, 2, 3]`

`find`
- `Collection::make([1, 3, 5])->find(fn(int $v) => $v > 2)` -> `3`
- `Collection::make([1, 3, 5])->find(fn(int $v) => $v > 9)` -> `null`

`groupBy`
- `Collection::make([['n' => 'a', 't' => 'x'], ['n' => 'b', 't' => 'x'], ['n' => 'c', 't' => 'y']])->groupBy(fn(array $i) => $i['t'])`
- Outcome: keys `x`, `y`, each value is a `Collection` of matching rows

`keyBy`
- `Collection::make([['id' => 10], ['id' => 20]])->keyBy(fn(array $i) => $i['id'])->getKeys()` -> `[10, 20]`

`take` / `drop`
- `Collection::make([1, 2, 3, 4])->take(2)->getValues()` -> `[1, 2]`
- `Collection::make([1, 2, 3, 4])->drop(2)->getValues()` -> `[3, 4]`

`merge`
- `Collection::make([3, 4])->merge(Collection::make([1, 2]))->getValues()` -> `[1, 2, 3, 4]`

`append` (mutates current collection)
- `$c = Collection::make([1]); $c->append(Collection::make([2, 3])); $c->getValues()` -> `[1, 2, 3]`

`copy`
- `$a = Collection::make([1]); $b = $a->copy();` -> same values, different instance

`usort` / `sort`
- `Collection::make([2, 1, 3])->sort(fn(int $a, int $b) => $a <=> $b)->getValues()` -> `[1, 2, 3]`

`flatten`
- `Collection::make([[1, 2], 3, [4]])->flatten()->getValues()` -> `[1, 2, 3, 4]`

`flip`
- `Collection::make(['a' => 'x', 'b' => 'y'])->flip()->toArray()` -> `['x' => 'a', 'y' => 'b']`

`unique`
- `Collection::make([1, 1, 2, 2])->unique()->getValues()` -> `[1, 2]`

`intersect`
- `Collection::make([1, 2, 3])->intersect(Collection::make([2, 4, 3]))->getValues()` -> `[2, 3]`

`diff`
- `Collection::make([1, 2, 3])->diff(Collection::make([2, 4]))->getValues()` -> `[1, 3]`

`reduce`
- `Collection::make([1, 2, 3])->reduce(fn(array $acc, int $v) => [...$acc, $v * 2], [])->getValues()` -> `[2, 4, 6]`

`sum`
- `Collection::make([['amount' => 1.5], ['amount' => 2.5]])->sum(fn(array $i) => $i['amount'], 1)` -> `5.0`

`slice`
- `Collection::make([1, 2, 3, 4])->slice(1, 2)->getValues()` -> `[2, 3]`
