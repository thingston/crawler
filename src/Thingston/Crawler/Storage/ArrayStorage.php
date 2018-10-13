<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Storage;

use InvalidArgumentException;
use Thingston\Crawler\Crawlable\CrawlableInterface;

/**
 * Array storage.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class ArrayStorage implements StorageInterface
{

    /**
     * @var array
     */
    protected $elements = [];

    /**
     * Create new instance.
     *
     * @param array $crawlables
     * @throws InvalidArgumentException
     */
    public function __construct(array $crawlables = [])
    {
        foreach ($crawlables as $crawlable) {
            if (false === $crawlable instanceof CrawlableInterface) {
                throw new InvalidArgumentException(sprintf('Invalid element type; it must be an instance of "%s".', CrawlableInterface::class));
            }

            $key = $crawlable->getKey();
            $this->elements[$key] = $crawlable;
        }
    }

    /**
     * Clear the storage by removing all elements.
     *
     * @return StorageInterface
     */
    public function clear(): StorageInterface
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Check either storage is empty..
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Whether an offset exists
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->elements[$key]);
    }

    /**
     * Offset to retrieve
     *
     * @param string $key
     * @return CrawlableInterface|null
     */
    public function offsetGet($key)
    {
        return $this->elements[$key] ?? null;
    }

    /**
     * Assign a value to the specified offset
     *
     * @param string $key
     * @param CrawlableInterface $crawlable
     */
    public function offsetSet($key, $crawlable)
    {
        if (false === $crawlable instanceof CrawlableInterface) {
            throw new InvalidArgumentException(sprintf('Invalid element type; it must be an instance of "%s".', CrawlableInterface::class));
        }

        $this->elements[$key] = $crawlable;
    }

    /**
     * Unset an offset
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->elements[$key]);
    }

    /**
     * Return total number of elements stored.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->elements);
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return false !== current($this->elements);
    }

    /**
     * Return the current element.
     *
     * @return CrawlableInterface
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * Return the key of the current element.
     *
     * @return string
     */
    public function key(): string
    {
        return key($this->elements);
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next()
    {
        next($this->elements);
    }
}
