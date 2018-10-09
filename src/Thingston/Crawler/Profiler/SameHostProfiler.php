<?php

namespace Thingston\Crawler\Profiler;

use Thingston\Crawler\Crawlable\CrawlableInterface;

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
