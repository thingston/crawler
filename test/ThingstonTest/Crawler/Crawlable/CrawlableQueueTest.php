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
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawlable\CrawlableQueue;
use Thingston\Crawler\Storage\ArrayStorage;
use Thingston\Crawler\Storage\StorageInterface;
use Thingston\Crawler\UriFactory;

class CrawlableQueueTest extends TestCase
{

    public function testSimpleConstruct()
    {
        $queue = new CrawlableQueue();
        $this->assertInstanceOf(StorageInterface::class, $queue->getStorage());

        $storage = new ArrayStorage();
        $queue->setStorage($storage);
        $this->assertSame($storage, $queue->getStorage());
    }

    public function testConstructWithArgument()
    {
        $storage = new ArrayStorage();
        $queue = new CrawlableQueue($storage);
        $queue->setStorage($storage);
        $this->assertSame($storage, $queue->getStorage());
    }

    public function testEnqueueDequeue()
    {
        $queue = new CrawlableQueue();

        $this->assertCount(0, $queue);
        $this->assertTrue($queue->isEmpty());
        $this->assertNull($queue->dequeue());

        $crawlable1 = new Crawlable(UriFactory::create('http://example.org'));
        $queue->enqueue($crawlable1);

        $this->assertCount(1, $queue);

        $queue->enqueue($crawlable1);
        $this->assertCount(1, $queue);

        $crawlable2 = new Crawlable(UriFactory::create('http://example.net'));
        $queue->enqueue($crawlable2);
        $this->assertCount(2, $queue);

        $this->assertSame($crawlable1, $queue->dequeue());
        $this->assertSame($crawlable2, $queue->dequeue());
        $this->assertNull($queue->dequeue());
        $this->assertTrue($queue->isEmpty());

        $queue->enqueue($crawlable1)->enqueue($crawlable2);
        $this->assertFalse($queue->isEmpty());

        $queue->clear();
        $this->assertTrue($queue->isEmpty());
    }
}
