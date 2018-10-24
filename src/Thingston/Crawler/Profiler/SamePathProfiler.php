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

use Thingston\Crawler\Crawlable\CrawlableInterface;

/**
 * Same path profiler.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class SamePathProfiler extends SameHostProfiler implements ProfilerInterface
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

        if (false === parent::crawl($crawlable)) {
            return false;
        }

        $parentPath = $parent->getUri()->getPath();

        if ('/' === substr($parentPath, -1)) {
            $parentPath .= '.';
        }

        $requiredPath = rtrim(dirname($parentPath), '/') . '/';

        return $requiredPath === substr($crawlable->getUri()->getPath(), 0, strlen($requiredPath));
    }
}
