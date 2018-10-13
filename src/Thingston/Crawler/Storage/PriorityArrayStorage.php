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
 * Priority array storage.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class PriorityArrayStorage extends ArrayStorage implements StorageInterface
{

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

            uasort($this->elements, [$this, 'comparePriority']);
        }
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

        uasort($this->elements, [$this, 'comparePriority']);
    }

    protected function comparePriority(CrawlableInterface $a, CrawlableInterface $b)
    {
        if ($a->getPriority() === $b->getPriority()) {
            return 0;
        }

        return $a->getPriority() < $b->getPriority();
    }
}
