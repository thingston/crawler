<?php

/**
 * Thingston Crawler
 *
 * @version 0.3.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Profiler;

use Thingston\Crawler\Crawlable\CrawlableInterface;

/**
 * Same host profiler.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class SameHostProfiler implements ProfilerInterface
{

    /**
     * Check either a given URI should crawl.
     *
     * @param CrawlableInterface $crawlable
     * @return bool
     */
    public function crawl(CrawlableInterface $crawlable): bool
    {
        if (null === $parent = $crawlable->getParent()) {
            return true;
        }

        return $crawlable->getUri()->getHost() === $parent->getUri()->getHost();
    }
}
