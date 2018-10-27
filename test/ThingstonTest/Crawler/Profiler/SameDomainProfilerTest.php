<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Profiler;

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Profiler\SameDomainProfiler;
use Thingston\Crawler\UriFactory;

class SameDomainProfilerTest extends TestCase
{

    public function testSameDomain()
    {
        $profiler = new SameDomainProfiler();
        $parent = new Crawlable(UriFactory::create('http://example.org'));

        $crawlable = new Crawlable(UriFactory::create('http://sub.example.org'));
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('http://sub.example.org'), $parent);
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('http://sub.example.com'), $parent);
        $this->assertFalse($profiler->crawl($crawlable));
    }
}
