<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Observer;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\Observer\NullObserver;

class NullObserverTest extends TestCase
{
    public function testNullObserverInstance()
    {
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $reason = $this->getMockBuilder(Exception::class)->getMock();
        $crawlable = $this->getMockBuilder(CrawlableInterface::class)->getMock();
        $crawler = $this->getMockBuilder(Crawler::class)->disableOriginalConstructor()->getMock();

        $observer = new NullObserver();

        $this->assertNull($observer->request($request, $crawlable, $crawler));
        $this->assertNull($observer->fulfilled($response, $crawlable, $crawler));
        $this->assertNull($observer->rejected($reason, $crawlable, $crawler));
    }
}
