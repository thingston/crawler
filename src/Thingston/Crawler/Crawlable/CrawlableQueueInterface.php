<?php

namespace Thingston\Crawler\Crawlable;

use Countable;
use Thingston\Crawler\Storage\StorageAwareInterface;

interface CrawlableQueueInterface extends StorageAwareInterface, Countable
{

    /**
     * Push a new element into the end of the queue.
     *
     * @param CrawlableInterface $crawlable
     * @return Crawlable\CrawlableQueueInterface
     */
    public function enqueue(CrawlableInterface $crawlable): CrawlableQueueInterface;

    /**
     * Shift first element off the queue.
     *
     * @return CrawlableInterface|null
     */
    public function dequeue(): ?CrawlableInterface;
}
