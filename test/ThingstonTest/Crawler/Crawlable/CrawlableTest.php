<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Crawlable;

use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
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

    public function testStartDurantionAndCreated()
    {
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));

        $start = microtime(true);
        $duration = rand(1000, 10000) / 1000;

        $this->assertNull($crawlable->getStart());
        $this->assertNull($crawlable->getDuration());
        $this->assertNull($crawlable->getCrawled());

        $crawlable->setStart($start)->setDuration($duration);

        $this->assertEquals($start, $crawlable->getStart());
        $this->assertEquals($duration, $crawlable->getDuration());
        $this->assertInstanceOf(\DateTimeInterface::class, $crawlable->getCrawled());

        $crawled = new DateTime(date('c', $start + $duration));
        $crawlable->setCrawled($crawled);
        $this->assertSame($crawled, $crawlable->getCrawled());
    }

    public function testStatusHeadersAndBody()
    {
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));

        $this->assertNull($crawlable->getStatus());
        $this->assertNull($crawlable->getHeaders());
        $this->assertNull($crawlable->getHeaders());
        $this->assertNull($crawlable->getBody());

        $status = 200;
        $headers = ['Date' => date('c')];
        $body = $this->getMockBuilder(StreamInterface::class)->getMock();

        $crawlable->setStatus($status)->setHeaders($headers)->setBody($body);

        $this->assertEquals($status, $crawlable->getStatus());
        $this->assertEquals($headers, $crawlable->getHeaders());
        $this->assertSame($body, $crawlable->getBody());
    }

    public function testPeriodicity()
    {
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));

        $this->assertEquals(Crawlable::PERIODICITY_ALWAYS, $crawlable->getPeriodicity());
        $this->assertTrue($crawlable->isPeriodicity());

        $periods = [1, 24, 7 * 24, 30 * 24, 365 * 24];
        $crawlable->setCrawled(new \DateTime());

        foreach ($periods as $period) {
            $crawlable->setPeriodicity(Crawlable::PERIODICITY_HOURLY);
            $this->assertFalse($crawlable->isPeriodicity());
            $crawlable->setPeriodicity(Crawlable::PERIODICITY_DAILY);
            $this->assertFalse($crawlable->isPeriodicity());
            $crawlable->setPeriodicity(Crawlable::PERIODICITY_WEEKLY);
            $this->assertFalse($crawlable->isPeriodicity());
            $crawlable->setPeriodicity(Crawlable::PERIODICITY_MONTHLY);
            $this->assertFalse($crawlable->isPeriodicity());
            $crawlable->setPeriodicity(Crawlable::PERIODICITY_YEARLY);
            $this->assertFalse($crawlable->isPeriodicity());
        }
    }

    public function testPriority()
    {
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));

        $this->assertNull($crawlable->getPriority());

        $priotity = 50;
        $crawlable->setPriority($priotity);
        $this->assertEquals($priotity, $crawlable->getPriority());
    }
}
