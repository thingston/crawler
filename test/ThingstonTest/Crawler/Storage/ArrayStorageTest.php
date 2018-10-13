<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Storage;

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Storage\ArrayStorage;
use Thingston\Crawler\UriFactory;

class ArrayStorageTest extends TestCase
{

    public function testArrayAccess()
    {
        $storage = new ArrayStorage();
        $this->assertCount(0, $storage);

        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $key = $crawlable->getKey();

        $storage[$key] = $crawlable;

        $this->assertCount(1, $storage);
        $this->assertTrue(isset($storage[$key]));
        $this->assertSame($crawlable, $storage[$key]);

        unset($storage[$key]);
        $this->assertCount(0, $storage);
        $this->assertFalse(isset($storage[$key]));
    }

    public function testIterator()
    {
        $crawlables = [];

        for ($i = 0; $i < 5; $i++) {
            $uri = UriFactory::create('http://example.org/page' . $i);
            $crawlables[] = new Crawlable($uri);
        }

        $storage = new ArrayStorage($crawlables);

        $this->assertCount(count($crawlables), $storage);

        $i = 0;

        foreach ($storage as $crawlable) {
            $this->assertSame($crawlables[$i], $crawlable);
            $i++;
        }
    }

    public function testEmptyness()
    {
        $storage = new ArrayStorage();
        $this->assertTrue($storage->isEmpty());

        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $key = $crawlable->getKey();

        $storage[$key] = $crawlable;

        $this->assertFalse($storage->isEmpty());

        $storage->clear();
        $this->assertTrue($storage->isEmpty());
    }
}
