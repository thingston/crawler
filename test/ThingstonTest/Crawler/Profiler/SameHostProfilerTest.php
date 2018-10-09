<?php

namespace Thingston\Crawler\Profiler;

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Profiler\SameHostProfiler;
use Thingston\Crawler\UriFactory;

class SameHostProfilerTest extends TestCase
{

    public function testSameHost()
    {
        $profiler = new SameHostProfiler();
        $parent = new Crawlable(UriFactory::create('http://example.org'));

        $crawlable = new Crawlable(UriFactory::create('http://example.org/page.html'));
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('http://example.org/page.html'), $parent);
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('http://example.com/page.html'), $parent);
        $this->assertFalse($profiler->crawl($crawlable));
    }
}
