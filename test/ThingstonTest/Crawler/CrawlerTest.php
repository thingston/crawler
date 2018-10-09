<?php

/**
 * Thingston Crawler
 *
 * @version 0.1.0
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
        $crawler = (new Crawler())->setUserAgent($userAgent);
        $this->assertEquals($userAgent, $crawler->getUserAgent());
    }

    public function testTimeout()
    {
        $timeout = 120;
        $crawler = (new Crawler())->setTimeout($timeout);
        $this->assertEquals($timeout, $crawler->getTimeout());
    }

    public function testConcurrency()
    {
        $concurrency = 20;
        $crawler = (new Crawler())->setConcurrency($concurrency);
        $this->assertEquals($concurrency, $crawler->getConcurrency());
    }

    public function testLimit()
    {
        $limit = 1000;
        $crawler = (new Crawler())->setLimit($limit);
        $this->assertEquals($limit, $crawler->getLimit());
    }

    public function testDepth()
    {
        $depth = 3;
        $crawler = (new Crawler())->setDepth($depth);
        $this->assertEquals($depth, $crawler->getDepth());
    }

    public function testClient()
    {
        $crawler = new Crawler();
        $this->assertInstanceOf(ClientInterface::class, $crawler->getClient());

        $client = new \GuzzleHttp\Client();
        $crawler->setClient($client);
        $this->assertSame($client, $crawler->getClient());
    }

    public function testCrawlingQueue()
    {
        $crawler = new Crawler();
        $this->assertInstanceOf(Crawlable\CrawlableQueueInterface::class, $crawler->getCrawlingQueue());

        $crawlingQueue = new Crawlable\CrawlableQueue();
        $crawler->setCrawlingQueue($crawlingQueue);
        $this->assertSame($crawlingQueue, $crawler->getCrawlingQueue());
    }

    public function testCrawledCollection()
    {
        $crawler = new Crawler();
        $this->assertInstanceOf(Crawlable\CrawlableCollectionInterface::class, $crawler->getCrawledCollection());

        $crawledCollection = new Crawlable\CrawlableCollection();
        $crawler->setCrawledCollection($crawledCollection);
        $this->assertSame($crawledCollection, $crawler->getCrawledCollection());
    }

    public function testProfiler()
    {
        $crawler = new Crawler();
        $this->assertInstanceOf(Profiler\ProfilerInterface::class, $crawler->getProfiler());

        $profiler = new Profiler\SameHostProfiler();
        $crawler->setProfiler($profiler);
        $this->assertSame($profiler, $crawler->getProfiler());
    }

    public function testObservers()
    {
        $crawler = new Crawler();
        $this->assertCount(0, $crawler->getObservers());

        $observer = new Observer\NullObserver();
        $crawler->addObserver($observer);
        $this->assertCount(1, $crawler->getObservers());
        $this->assertTrue($crawler->hasObserver($observer));
    }

    public function testCrawlWithSuccess()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects($this->once())->method('getStatusCode')->willReturn(200);

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client->expects($this->once())->method('request')->willReturn($response);

        $crawler = new Crawler();
        $crawler->setClient($client);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));

        $this->assertNull($crawlable->getCrawled());
        $this->assertNull($crawlable->getStatus());

        $this->assertSame($response, $crawler->crawl($crawlable));

        $this->assertInstanceOf(\DateTimeInterface::class, $crawlable->getCrawled());
        $this->assertEquals(200, $crawlable->getStatus());
    }

    public function testCrawlWithClientException()
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects($this->any())->method('getStatusCode')->willReturn(404);

        $exception = new ClientException('Page not found.', $request, $response);

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client->expects($this->once())->method('request')->willThrowException($exception);

        $crawler = new Crawler();
        $crawler->setClient($client);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));

        $this->assertNull($crawlable->getCrawled());
        $this->assertNull($crawlable->getStatus());

        $this->assertSame($response, $crawler->crawl($crawlable));

        $this->assertInstanceOf(\DateTimeInterface::class, $crawlable->getCrawled());
        $this->assertEquals(404, $crawlable->getStatus());
    }

    public function testCrawlWithRequestExceptionHavingResponse()
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects($this->any())->method('getStatusCode')->willReturn(400);

        $exception = new RequestException('Bad request', $request, $response);

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client->expects($this->once())->method('request')->willThrowException($exception);

        $crawler = new Crawler();
        $crawler->setClient($client);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));

        $this->assertNull($crawlable->getCrawled());
        $this->assertNull($crawlable->getStatus());

        $this->assertSame($response, $crawler->crawl($crawlable));

        $this->assertInstanceOf(\DateTimeInterface::class, $crawlable->getCrawled());
        $this->assertEquals(400, $crawlable->getStatus());
    }

    public function testCrawlWithRequestExceptionNotHavingResponse()
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();

        $exception = new RequestException('Bad request', $request);

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client->expects($this->once())->method('request')->willThrowException($exception);

        $crawler = new Crawler();
        $crawler->setClient($client);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));

        $this->expectException(get_class($exception));
        $crawler->crawl($crawlable);
    }

    public function testCrawlWithException()
    {
        $exception = new \Exception('This is a bug');

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client->expects($this->once())->method('request')->willThrowException($exception);

        $crawler = new Crawler();
        $crawler->setClient($client);

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));

        $this->expectException(get_class($exception));
        $crawler->crawl($crawlable);
    }

    public function testFulfilled()
    {
        $crawler = new Crawler();

        $observer = new Observer\NullObserver();
        $crawler->addObserver($observer);

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));
        $crawler->getCrawledCollection()->add($crawlable);

        $this->assertNull($crawler->fulfilled($response, $crawlable->getKey()));
    }

    public function testRejected()
    {
        $crawler = new Crawler();

        $observer = new Observer\NullObserver();
        $crawler->addObserver($observer);

        $reason = $this->getMockBuilder(TransferException::class)->getMock();

        $crawlable = new Crawlable\Crawlable(UriFactory::create('http://example.org'));
        $crawler->getCrawledCollection()->add($crawlable);

        $this->assertNull($crawler->rejected($reason, $crawlable->getKey()));
    }
}
