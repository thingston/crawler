<?php

namespace Thingston\Crawler\Crawlable;

use Countable;
use Iterator;
use Thingston\Crawler\Storage\StorageAwareInterface;

interface CrawlableCollectionInterface extends StorageAwareInterface, Countable, Iterator
{

    /**
     * Check either an element key is stored.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Add element into storage.
     *
     * @param CrawlableInterface $crawlable
     * @return CrawlableCollectionInterface
     */
    public function add(CrawlableInterface $crawlable): CrawlableCollectionInterface;

    /**
     * Set element into storage.
     *
     * @param string $key
     * @param CrawlableInterface $crawlable
     * @return CrawlableCollectionInterface
     */
    public function set(string $key, CrawlableInterface $crawlable): CrawlableCollectionInterface;

    /**
     * Retrieve an element by key.
     *
     * @param string $key
     * @return CrawlableInterface|null
     */
    public function get(string $key): ?CrawlableInterface;

    /**
     * Remove an element from storage.
     *
     * @param string $key
     * @return CrawlableCollectionInterface
     */
    public function remove(string $key): CrawlableCollectionInterface;
}
