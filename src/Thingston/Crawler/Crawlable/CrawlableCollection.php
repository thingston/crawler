<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use Traversable;
use Thingston\Crawler\Storage\ArrayStorage;
use Thingston\Crawler\Storage\StorageAwareTrait;
use Thingston\Crawler\Storage\StorageInterface;

/**
 * Crawlable collection.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class CrawlableCollection implements CrawlableCollectionInterface
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
        $this->setStorage($storage ?? new ArrayStorage());
    }

    /**
     * Check either an element key is stored.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->getStorage()[$key]);
    }

    /**
     * Add element into storage.
     *
     * @param CrawlableInterface $crawlable
     * @return CrawlableCollectionInterface
     */
    public function add(CrawlableInterface $crawlable): CrawlableCollectionInterface
    {
        return $this->set($crawlable->getKey(), $crawlable);
    }

    /**
     * Set element into storage.
     *
     * @param string $key
     * @param CrawlableInterface $crawlable
     * @return CrawlableCollectionInterface
     */
    public function set(string $key, CrawlableInterface $crawlable): CrawlableCollectionInterface
    {
        $storage = $this->getStorage();
        $storage[$key] = $crawlable;

        return $this;
    }

    /**
     * Retrieve an element by key.
     *
     * @param string $key
     * @return CrawlableInterface|null
     */
    public function get(string $key): ?CrawlableInterface
    {
        return $this->getStorage()[$key] ?? null;
    }

    /**
     * Remove an element from storage.
     *
     * @param string $key
     * @return CrawlableCollectionInterface
     */
    public function remove(string $key): CrawlableCollectionInterface
    {
        unset($this->getStorage()[$key]);

        return $this;
    }

    /**
     * Count how many elements are in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->getStorage()->count();
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     */
    public function rewind()
    {
        $this->storage->rewind();
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->storage->valid();
    }

    /**
     * Return the current element.
     *
     * @return CrawlableInterface
     */
    public function current()
    {
        return $this->storage->current();
    }

    /**
     * Return the key of the current element.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->storage->key();
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next()
    {
        $this->storage->next();
    }
}
