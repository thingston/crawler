<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Profiler;

use Purl\Url;
use Thingston\Crawler\Crawlable\CrawlableInterface;

/**
 * Same domain profiler.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class SameDomainProfiler implements ProfilerInterface
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

        $parentDomain = (new Url($parent->getUri()))->registerableDomain;
        $currentDomain = (new Url($crawlable->getUri()))->registerableDomain;

        return $parentDomain === $currentDomain;
    }
}
