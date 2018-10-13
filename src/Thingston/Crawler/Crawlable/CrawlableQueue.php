<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Storage\PriorityArrayStorage;
use Thingston\Crawler\Storage\StorageAwareTrait;
use Thingston\Crawler\Storage\StorageInterface;

/**
 * Crawlable queue.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class CrawlableQueue implements CrawlableQueueInterface
{

    /**
     * Implements StorageAwareInterface
     */
    use StorageAwareTrait;

    /**
     * Create new instance.
     *
     * @param StorageInterface|null $storage
     */
    public function __construct(StorageInterface $storage = null)
    {
        $this->setStorage($storage ?? new PriorityArrayStorage());
    }

    /**
     * Push a new element to the queue.
     *
     * @param CrawlableInterface $crawlable
     * @return CrawlableQueueInterface
     */
    public function enqueue(CrawlableInterface $crawlable): CrawlableQueueInterface
    {
        $key = $crawlable->getKey();
        $this->storage[$key] = $crawlable;

        return $this;
    }

    /**
     * Shift first element of the queue.
     *
     * @return CrawlableInterface|null
     */
    public function dequeue(): ?CrawlableInterface
    {
        if (true === $this->storage->isEmpty()) {
            return null;
        }

        $this->storage->rewind();
        $crawlable = $this->storage->current();
        unset($this->storage[$crawlable->getKey()]);

        return $crawlable;
    }

    /**
     * Count how many elements are in the queue.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->storage->count();
    }
}
