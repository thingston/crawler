<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\Observer;
use Thingston\Crawler\Profiler;
use Thingston\Crawler\UriFactory;

class CrawlerTest extends TestCase
{

    public function testUserAgent()
    {
        $userAgent = 'MyCrawler/1.0';
            $crawler = (new Crawler('MyBot/1.0'))->setUserAgent($userAgent);
        $this->assertEquals($userAgent, $crawler->getUserAgent());
    }

    public function testTimeout()
    {
        $timeout = 120;
        $crawler = (new Crawler('MyBot/1.0'))->setTimeout($timeout);
        $this->assertEquals($timeout, $crawler->getTimeout());
    }

    public function testConcurrency()
    {
        $concurrency = 20;
        $crawler = (new Crawler('MyBot/1.0'))->setConcurrency($concurrency);
        $this->assertEquals($concurrency, $crawler->getConcurrency());
    }

    public function testLimit()
    {
        $limit = 1000;
        $crawler = (new Crawler('MyBot/1.0'))->setLimit($limit);
        $this->assertEquals($limit, $crawler->getLimit());
    }

    public function testDepth()
    {
        $depth = 3;
        $crawler = (new Crawler('MyBot/1.0'))->setDepth($depth);
        $this->assertEquals($depth, $crawler->getDepth());
    }

    public function testClient()
    {
        $crawler = new Crawler('MyBot/1.0');
        $this->assertInstanceOf(ClientInterface::class, $crawler->getClient());

        $client = new \GuzzleHttp\Client();
        $crawler->setClient($client);
        $this->assertSame($client, $crawler->getClient());
    }

    public function testCrawlingQueue()
    {
        $crawler = new Crawler('MyBot/1.0');
        $this->assertInstanceOf(Crawlable\CrawlableQueueInterface::class, $crawler->getCrawlingQueue());

        $crawlingQueue = new Crawlable\CrawlableQueue();
        $crawler->setCrawlingQueue($crawlingQueue);
        $this->assertSame($crawlingQueue, $crawler->getCrawlingQueue());
    }

    public function testCrawledCollection()
    {
        $crawler = new Crawler('MyBot/1.0');
        $this->assertInstanceOf(Crawlable\CrawlableCollectionInterface::class, $crawler->getCrawledCollection());

        $crawledCollection = new Crawlable\CrawlableCollection();
        $crawler->setCrawledCollection($crawledCollection);
        $this->assertSame($crawledCollection, $crawler->getCrawledCollection());
    }

    public function testProfiler()
    {
        $crawler = new Crawler('MyBot/1.0');
        $this->assertInstanceOf(Profiler\ProfilerInterface::class, $crawler->getProfiler());

        $profiler = new Profiler\SameHostProfiler();
        $crawler->setProfiler($profiler);
        $this->assertSame($profiler, $crawler->getProfiler());
    }

    public function testObservers()
    {
        $crawler = new Crawler('MyBot/1.0');
        $this->assertCount(1, $crawler->getObservers());

        $observer = new Observer\NullObserver();
        $crawler->addObserver($observer);
        $this->assertCount(2, $crawler->getObservers());
        $this->assertTrue($crawler->hasObserver($observer));
    }

    public function testCrawl()
    {
        $body = $this->getMockBuilder(StreamInterface::class)->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects($this->any())->method('getStatusCode')->willReturn(200);
        $response->expects($this->any())->method('getHeaders')->willReturn([]);
        $response->expects($this->any())->method('getBody')->willReturn($body);

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client->expects($this->any())->method('request')->willReturn($response);

        $crawler = new Crawler('MyBot/1.0');
        $crawler->setClient($client);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));

        $this->assertNull($crawlable->getStart());
        $this->assertNull($crawlable->getDuration());
        $this->assertNull($crawlable->getCrawled());
        $this->assertNull($crawlable->getStatus());

        $crawler->crawl($crawlable);

        $this->assertGreaterThan(0, $crawlable->getStart());
        $this->assertGreaterThan(0, $crawlable->getDuration());
        $this->assertInstanceOf(\DateTimeInterface::class, $crawlable->getCrawled());
        $this->assertEquals(200, $crawlable->getStatus());
    }

    public function testCrawlWithException()
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();

        $body = $this->getMockBuilder(StreamInterface::class)->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects($this->any())->method('getStatusCode')->willReturn(400);
        $response->expects($this->any())->method('getHeaders')->willReturn([]);
        $response->expects($this->any())->method('getBody')->willReturn($body);

        $exception = new RequestException('Bad request', $request, $response);

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client->expects($this->any())->method('request')->willThrowException($exception);

        $crawler = new Crawler('MyBot/1.0');
        $crawler->setClient($client);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));

        $this->assertNull($crawlable->getStatus());

        $crawler->crawl($crawlable);

        $this->assertEquals(400, $crawlable->getStatus());
    }

    public function testFulfilled()
    {
        $crawler = new Crawler('MyBot/1.0');

        $observer = new Observer\NullObserver();
        $crawler->addObserver($observer);

        $body = $this->getMockBuilder(StreamInterface::class)->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects($this->any())->method('getStatusCode')->willReturn(200);
        $response->expects($this->any())->method('getHeaders')->willReturn([]);
        $response->expects($this->any())->method('getBody')->willReturn($body);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));
        $crawler->getCrawledCollection()->add($crawlable);

        $this->assertNull($crawler->fulfilled($response, $crawlable->getKey()));
    }

    public function testRejected()
    {
        $crawler = new Crawler('MyBot/1.0');

        $observer = new Observer\NullObserver();
        $crawler->addObserver($observer);

        $reason = $this->getMockBuilder(TransferException::class)->getMock();

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));
        $crawler->getCrawledCollection()->add($crawlable);

        $this->assertNull($crawler->rejected($reason, $crawlable->getKey()));
    }
}
