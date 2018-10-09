<?php

namespace Thingston\Crawler\Profiler;

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Profiler\AllLinksProfiler;

class AllLinksProfilerTest extends TestCase
{
    public function testAlwaysReturnsTrue()
    {
        $profiler = new AllLinksProfiler();
        $crawlable = $this->getMockBuilder(CrawlableInterface::class)->getMock();
        $this->assertTrue($profiler->crawl($crawlable));
    }
}
