<?php

/**
 * Thingston Crawler
 *
 * @version 0.1.1
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use Countable;
use Thingston\Crawler\Storage\StorageAwareInterface;

/**
 * Crawlable queue interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
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
