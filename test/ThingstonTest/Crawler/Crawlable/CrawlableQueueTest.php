<?php

namespace ThingstonTest\Crawler\Crawlable;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Thingston\Crawler\Crawlable\CrawlableQueue;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\UriFactory;
use Thingston\Crawler\Storage\StorageInterface;

class CrawlableQueueTest extends TestCase
{

    public function testStorageTrait()
    {
        $queue = new CrawlableQueue();
        $this->assertInstanceOf(StorageInterface::class, $queue->getStorage());

        $storage = $this->getMockBuilder(StorageInterface::class)->getMock();
        $queue->setStorage($storage);
        $this->assertSame($storage, $queue->getStorage());
    }
}
