<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

/**
 * Crawlable hydrator interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
interface CrawlableHydratorInterface
{

    /**
     * Hydrate a crawlable from an array of data.
     *
     * @param array $data
     * @param CrawlableInterface $crawlable
     * @return CrawlableInterface
     */
    public function hydrate(array $data, CrawlableInterface $crawlable = null): CrawlableInterface;

    /**
     * Extract a crawlable into an array.
     *
     * @param CrawlableInterface $crawlable
     * @return array
     */
    public function extract(CrawlableInterface $crawlable): array;
}
