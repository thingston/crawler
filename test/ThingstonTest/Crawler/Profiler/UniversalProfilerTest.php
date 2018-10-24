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

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Profiler\UniversalProfiler;

class UniversalProfilerTest extends TestCase
{
    public function testAlwaysReturnsTrue()
    {
        $profiler = new UniversalProfiler();
        $crawlable = $this->getMockBuilder(CrawlableInterface::class)->getMock();
        $this->assertTrue($profiler->crawl($crawlable));
    }
}
