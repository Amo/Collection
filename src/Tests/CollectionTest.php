<?php

namespace Amo\Collection\Tests;

use Amo\Collection\Collection;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testStaticInstantiation()
    {
        $this->assertInstanceOf(
            Collection::class,
            Collection::make([])
        );

        $items = range(0,10);
        $this->assertEquals(
            count($items),
            Collection::make($items)->count()
        );
    }

    public function testMap()
    {
        $map = function($item) {
            return $item.'-mapped';
        };

        $collection = Collection::make(['a','b','c'])->map($map);
        $this->assertContains('c-mapped', $collection->getValues());
    }

    public function testEach()
    {
        /** @var \Closure $shouldBeCalled */
        $shouldBeCalled = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $shouldBeCalled
            ->expects($this->exactly(5))
            ->method('__invoke');

        $items = [true, null, 0, 1, false, 'never_processed'];
        Collection::make($items)->each(function($value) use ($shouldBeCalled) {
            $shouldBeCalled();

            return $value;
        });
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
        $colSort = $col->usort(function($a, $b) {
            return $a < $b;
        });

        $this->assertEquals($colSort->getValues(), array_reverse($col->getValues(), false));
    }

    public function testSlice()
    {
        $col = Collection::make(range(0,10));
        $slice = $col->slice(0,5);
        $this->assertInstanceOf(Collection::class, $slice);
        $this->assertCount(5, $slice);
    }
}