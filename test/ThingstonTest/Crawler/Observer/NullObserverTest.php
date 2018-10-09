<?php

namespace ThingstonTest\Crawler\Observer;

use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\Observer\NullObserver;
use Thingston\Crawler\Observer\ObserverInterface;

class NullObserverTest extends TestCase
{
    public function testNullObserverInstance()
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $reason = $this->getMockBuilder(TransferException::class)->getMock();
        $crawlable = $this->getMockBuilder(CrawlableInterface::class)->getMock();
        $crawler = $this->getMockBuilder(Crawler::class)->getMock();

        $observer = new NullObserver();

        $this->assertNull($observer->request($request, $crawlable, $crawler));
        $this->assertNull($observer->fulfilled($response, $crawlable, $crawler));
        $this->assertNull($observer->rejected($reason, $crawlable, $crawler));
    }
}
