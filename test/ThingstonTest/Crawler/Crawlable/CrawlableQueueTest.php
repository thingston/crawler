<?php

/**
 * Thingston Crawler
 *
 * @version 0.1.1
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Crawlable;

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\CrawlableQueue;
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
