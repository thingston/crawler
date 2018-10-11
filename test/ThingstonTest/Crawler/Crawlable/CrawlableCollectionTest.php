<?php

/**
 * Thingston Crawler
 *
 * @version 0.3.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Crawlable;

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\CrawlableCollection;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\UriFactory;
use Thingston\Crawler\Storage\StorageInterface;

class CrawlableCollectionTest extends TestCase
{

    public function testStorageTrait()
    {
        $collection = new CrawlableCollection();
        $this->assertInstanceOf(StorageInterface::class, $collection->getStorage());

        $storage = $this->getMockBuilder(StorageInterface::class)->getMock();
        $collection->setStorage($storage);
        $this->assertSame($storage, $collection->getStorage());
    }

    public function testCollectionContent()
    {
        $collection = new CrawlableCollection();
        $uri = UriFactory::create('http://example.org');
        $crawlable = new Crawlable($uri);
        $key = $crawlable->getKey();

        $this->assertFalse($collection->has($key));
        $collection->add($crawlable);
        $this->assertTrue($collection->has($key));
        $this->assertSame($crawlable, $collection->get($key));

        $collection->remove($key);
        $this->assertFalse($collection->has($key));
    }

    public function testEmptyness()
    {
        $collection = new CrawlableCollection();
        $this->assertTrue($collection->isEmpty());
        $this->assertCount(0, $collection);

        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $collection->add($crawlable);

        $this->assertFalse($collection->isEmpty());
        $this->assertCount(1, $collection);

        $collection->clear();
        $this->assertTrue($collection->isEmpty());
    }

    public function testIterator()
    {
        $crawlables = [];

        for ($i = 0; $i < 5; $i++) {
            $uri = UriFactory::create('http://example.org/page' . $i);
            $crawlables[] = new Crawlable($uri);
        }

        $collection = new CrawlableCollection();

        foreach ($crawlables as $crawlable) {
            $collection->add($crawlable);
        }

        $this->assertCount(count($crawlables), $collection);

        $i = 0;

        foreach ($collection as $crawlable) {
            $this->assertSame($crawlables[$i], $crawlable);
            $i++;
        }

        $this->assertCount(count($crawlables), $collection);
    }
}
