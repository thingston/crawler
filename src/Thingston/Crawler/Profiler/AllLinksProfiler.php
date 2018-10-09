<?php

namespace Thingston\Crawler\Profiler;

use Thingston\Crawler\Crawlable\CrawlableInterface;

class AllLinksProfiler implements ProfilerInterface
{

    /**
     * Check either a given URI should crawl.
     *
     * @param CrawlableInterface $crawlable
     * @return bool
     */
    public function crawl(CrawlableInterface $crawlable): bool
    {
        return true;
    }
}
