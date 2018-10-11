<?php

/**
 * Thingston Crawler
 *
 * @version 0.3.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use Countable;
use Iterator;
use Thingston\Crawler\Storage\StorageAwareInterface;

/**
 * Crawlable collection interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
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
