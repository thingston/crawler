<?php

namespace ThingstonTest\Crawler\Crawlable;

use DateTime;
use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\UriFactory;

class CrawlableTest extends TestCase
{

    public function testConstruct()
    {
        $uri = UriFactory::create('http://example.org/path/to/file.html');
        $key = UriFactory::hash($uri);

        $crawlable = new Crawlable($uri);

        $this->assertSame($uri, $crawlable->getUri());
        $this->assertSame($key, $crawlable->getKey());
        $this->assertSame(0, $crawlable->getDepth());
        $this->assertNull($crawlable->getParent());
    }

    public function testGetParent()
    {
        $uri = UriFactory::create('http://example.org/path/to/file.html');
        $parent = new Crawlable(UriFactory::create('http://example.org/path/to/parent.html'));

        $crawlable = new Crawlable($uri, $parent);

        $this->assertSame($uri, $crawlable->getUri());
        $this->assertSame($parent, $crawlable->getParent());
    }

    public function testGetDepth()
    {
        $parent0 = new Crawlable(UriFactory::create(''));
        $parent1 = new Crawlable(UriFactory::create(''), $parent0);
        $parent2 = new Crawlable(UriFactory::create(''), $parent1);
        $parent3 = new Crawlable(UriFactory::create(''), $parent2);

        $this->assertEquals(0, $parent0->getDepth());
        $this->assertEquals(1, $parent1->getDepth());
        $this->assertEquals(2, $parent2->getDepth());
        $this->assertEquals(3, $parent3->getDepth());
    }

    public function testCrawledDateTimeAndStatus()
    {
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $crawled = new DateTime();
        $status = 200;

        $crawlable->setCrawled($crawled)->setStatus($status);

        $this->assertEquals($crawled, $crawlable->getCrawled());
        $this->assertEquals($status, $crawlable->getStatus());
    }
}
