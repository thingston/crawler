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
use Thingston\Crawler\Profiler\SimilarHostProfiler;
use Thingston\Crawler\UriFactory;

class SimilarHostProfilerTest extends TestCase
{

    public function testSimilarHost()
    {
        $profiler = new SimilarHostProfiler();
        $parent = new Crawlable(UriFactory::create('http://example.org:8080'));

        $crawlable = new Crawlable(UriFactory::create('http://example.org/page.html'));
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('https://example.org/page.html'), $parent);
        $this->assertTrue($profiler->crawl($crawlable));

        $crawlable = new Crawlable(UriFactory::create('http://example.com/page.html'), $parent);
        $this->assertFalse($profiler->crawl($crawlable));
    }
}
