<?php
declare(strict_types=1);

namespace Amo\Collection\Tests;

use Amo\Collection\Collection;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testMake()
    {
        $collection = Collection::make([]);
        $this->assertInstanceOf(Collection::class, $collection);
    }

    public function testStaticInstantiation()
    {
        $items = range(0,10);
        $this->assertEquals(
            count($items),
            Collection::make($items)->count()
        );
    }

    public function testCreateAlias()
    {
        $collection = Collection::create(['a', 'b']);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame(['a', 'b'], $collection->getValues());
    }

    public function testMap()
    {
        $map = function($item) {
            return $item.'-mapped';
        };

        $collection = Collection::make(['a','b','c'])->map($map);
        $this->assertContains('c-mapped', $collection->getValues());
    }

    public function testFlatMap()
    {
        $flatMapped = Collection::make([[1, 2], [3]])->flatMap(function (array $items) {
            return $items;
        });

        $this->assertSame([1, 2, 3], $flatMapped->getValues());
    }

    public function testFind()
    {
        $found = Collection::make([1, 2, 3])->find(function (int $item) {
            return $item > 1;
        });
        $notFound = Collection::make([1, 2, 3])->find(function (int $item) {
            return $item > 9;
        });

        $this->assertSame(2, $found);
        $this->assertNull($notFound);
    }

    public function testGroupBy()
    {
        $items = Collection::make([
            ['name' => 'a', 'type' => 'x'],
            ['name' => 'b', 'type' => 'x'],
            ['name' => 'c', 'type' => 'y'],
        ]);
        $grouped = $items->groupBy(function (array $item) {
            return $item['type'];
        });

        $this->assertCount(2, $grouped);
        $this->assertSame(['a', 'b'], array_column($grouped->get('x')->getValues(), 'name'));
        $this->assertSame(['c'], array_column($grouped->get('y')->getValues(), 'name'));
    }

    public function testKeyBy()
    {
        $items = Collection::make([
            ['id' => 10, 'name' => 'a'],
            ['id' => 20, 'name' => 'b'],
        ]);
        $keyed = $items->keyBy(function (array $item) {
            return $item['id'];
        });

        $this->assertSame([10, 20], $keyed->getKeys());
        $this->assertSame('b', $keyed->get(20)['name']);
    }

    public function testTakeAndDrop()
    {
        $col = Collection::make([1, 2, 3, 4]);

        $this->assertSame([1, 2], $col->take(2)->getValues());
        $this->assertSame([3, 4], $col->drop(2)->getValues());
    }

    public function testEach()
    {
        $calls = 0;

        $items = [true, null, 0, 1, false, 'never_processed'];
        Collection::make($items)->each(function ($value) use (&$calls) {
            ++$calls;

            return $value;
        });

        $this->assertSame(6, $calls);
    }

    public function testMerge()
    {
        $col1 = Collection::make(range(1,5));
        $col2 = Collection::make(range(6,10));

        $colMerge = $col2->merge($col1);
        $this->assertEquals(range(1,10), $colMerge->getValues());
        $this->assertNotSame($col2, $colMerge);
        $this->assertNotSame($col1, $colMerge);
    }

    public function testAppend()
    {
        $col = Collection::make([1, 2]);
        $result = $col->append(Collection::make([3, 4]));

        $this->assertSame($col, $result);
        $this->assertSame([1, 2, 3, 4], $col->getValues());
    }


    public function testCopy()
    {
        $col = Collection::make(['a','b','c']);
        $colCopy = $col->copy();

        $this->assertNotSame($col, $colCopy);
        $this->assertSame($col->getValues(), $colCopy->getValues());
        $col->set(0, 'z');
        $this->assertNotSame($col->getValues(), $colCopy->getValues());
    }

    public function testUsort()
    {
        $col = Collection::make(range(0,10));
        $colSort = $col->usort(function ($a, $b) {
            return $b <=> $a;
        });

        $this->assertEquals($colSort->getValues(), array_reverse($col->getValues(), false));
    }

    public function testSortAlias()
    {
        $col = Collection::make([2, 1, 3]);
        $sorted = $col->sort(function ($a, $b) {
            return $a <=> $b;
        });

        $this->assertSame([1, 2, 3], $sorted->getValues());
        $this->assertSame([2, 1, 3], $col->getValues());
    }

    public function testFlatten()
    {
        $flat = Collection::make([[1, 2], 3, [4]])->flatten();
        $this->assertSame([1, 2, 3, 4], $flat->getValues());
    }

    public function testFlip()
    {
        $flipped = Collection::make(['a' => 'x', 'b' => 'y'])->flip();
        $this->assertSame(['x' => 'a', 'y' => 'b'], $flipped->toArray());
    }

    public function testFlipWithClosure()
    {
        $flipped = Collection::make(['a' => 'x'])->flip(function ($key, $value) {
            return $key.'-'.$value;
        });

        $this->assertSame(['x' => 'a-x'], $flipped->toArray());
    }

    public function testFlipThrowsOnNonScalar()
    {
        $this->expectException(\InvalidArgumentException::class);
        Collection::make([['x']])->flip();
    }

    public function testUnique()
    {
        $unique = Collection::make([1, 1, 2, 2, 3])->unique();
        $this->assertSame([1, 2, 3], $unique->getValues());
    }

    public function testUniqueWithComparator()
    {
        $items = [
            ['id' => 1, 'value' => 'a'],
            ['id' => 1, 'value' => 'b'],
            ['id' => 2, 'value' => 'c'],
        ];
        $unique = Collection::make($items)->unique(function ($a, $b) {
            return $a['id'] === $b['id'];
        });

        $this->assertCount(2, $unique);
    }

    public function testIntersect()
    {
        $intersection = Collection::make([1, 2, 3])->intersect(Collection::make([2, 4, 3]));

        $this->assertSame([2, 3], $intersection->getValues());
    }

    public function testIntersectWithComparator()
    {
        $left = Collection::make([
            ['id' => 1, 'name' => 'a'],
            ['id' => 2, 'name' => 'b'],
        ]);
        $right = Collection::make([
            ['id' => 2, 'name' => 'B'],
            ['id' => 3, 'name' => 'c'],
        ]);

        $intersection = $left->intersect($right, function (array $a, array $b) {
            return $a['id'] === $b['id'];
        });

        $this->assertSame([2], array_column($intersection->getValues(), 'id'));
    }

    public function testDiff()
    {
        $difference = Collection::make([1, 2, 3])->diff(Collection::make([2, 4]));

        $this->assertSame([1, 3], $difference->getValues());
    }

    public function testDiffWithComparator()
    {
        $left = Collection::make([
            ['id' => 1, 'name' => 'a'],
            ['id' => 2, 'name' => 'b'],
        ]);
        $right = Collection::make([
            ['id' => 2, 'name' => 'B'],
        ]);

        $difference = $left->diff($right, function (array $a, array $b) {
            return $a['id'] === $b['id'];
        });

        $this->assertSame([1], array_column($difference->getValues(), 'id'));
    }

    public function testReduce()
    {
        $reduced = Collection::make([1, 2, 3])->reduce(function (array $acc, int $value) {
            $acc[] = $value * 2;

            return $acc;
        }, []);

        $this->assertSame([2, 4, 6], $reduced->getValues());
    }

    public function testReduceThrowsWhenReducerDoesNotReturnArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        Collection::make([1, 2])->reduce(function (int $acc, int $value) {
            return $acc + $value;
        }, 0);
    }

    public function testSum()
    {
        $sum = Collection::make([['amount' => 1.5], ['amount' => 2.5]])->sum(function ($item) {
            return $item['amount'];
        }, 1);

        $this->assertSame(5.0, $sum);
    }

    public function testSlice()
    {
        $col = Collection::make(range(0,10));
        $slice = $col->slice(0,5);
        $this->assertInstanceOf(Collection::class, $slice);
        $this->assertCount(5, $slice);
    }

    public function testPublicApiMethodsHaveDedicatedTests()
    {
        $map = [
            'create' => ['testCreateAlias'],
            'make' => ['testMake', 'testStaticInstantiation'],
            'each' => ['testEach'],
            'map' => ['testMap'],
            'flatMap' => ['testFlatMap'],
            'find' => ['testFind'],
            'groupBy' => ['testGroupBy'],
            'keyBy' => ['testKeyBy'],
            'take' => ['testTakeAndDrop'],
            'drop' => ['testTakeAndDrop'],
            'merge' => ['testMerge'],
            'append' => ['testAppend'],
            'copy' => ['testCopy'],
            'usort' => ['testUsort'],
            'sort' => ['testSortAlias'],
            'flatten' => ['testFlatten'],
            'flip' => ['testFlip', 'testFlipWithClosure', 'testFlipThrowsOnNonScalar'],
            'unique' => ['testUnique', 'testUniqueWithComparator'],
            'intersect' => ['testIntersect', 'testIntersectWithComparator'],
            'diff' => ['testDiff', 'testDiffWithComparator'],
            'reduce' => ['testReduce', 'testReduceThrowsWhenReducerDoesNotReturnArray'],
            'sum' => ['testSum'],
            'slice' => ['testSlice'],
        ];

        foreach ($map as $method => $tests) {
            $this->assertTrue(method_exists(Collection::class, $method), sprintf('Method %s is missing', $method));
            foreach ($tests as $test) {
                $this->assertTrue(method_exists($this, $test), sprintf('Test %s for method %s is missing', $test, $method));
            }
        }
    }
}
