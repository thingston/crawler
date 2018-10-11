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
