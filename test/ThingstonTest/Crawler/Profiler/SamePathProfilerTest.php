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
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Profiler\SamePathProfiler;
use Thingston\Crawler\UriFactory;

class SamePathProfilerTest extends TestCase
{

    public function testSamePath()
    {
        $profiler = new SamePathProfiler();
        $parent = new Crawlable(UriFactory::create('http://example.org/some/deep/page1.html'));

        $crawlable = new Crawlable(UriFactory::create('http://example.org/some/deep/page2.html'));
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('http://example.org/some/deep/page2.html'), $parent);
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('http://example.org/not/some/deep/page1.html'), $parent);
        $this->assertFalse($profiler->crawl($crawlable));
    }
}
