<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Observer;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\Observer\RedirectionObserver;
use Thingston\Crawler\UriFactory;

class RedirectionObserverTest extends TestCase
{

    public function testRedirection()
    {
        $observer = new RedirectionObserver();
        $response = new Response(301, ['Location' => 'http://www.example.org']);
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $crawler = new Crawler('MyBot/1.0');

        $this->assertEmpty($crawler->getCrawlingQueue());
        $observer->fulfilled($response, $crawlable, $crawler);
        $key = UriFactory::hash('http://www.example.org');
        $this->assertNotEmpty($crawler->getCrawlingQueue());
        $this->assertEquals($key, $crawler->getCrawlingQueue()->dequeue()->getKey());
    }

    public function testLoopRedirection()
    {
        $observer = new RedirectionObserver();
        $response = new Response(301, ['Location' => 'http://example.org']);
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $crawler = new Crawler('MyBot/1.0');

        $this->assertEmpty($crawler->getCrawlingQueue());
        $observer->fulfilled($response, $crawlable, $crawler);
        $this->assertEmpty($crawler->getCrawlingQueue());
    }

    public function testDuplicatedRedirection()
    {
        $observer = new RedirectionObserver();
        $response = new Response(301, ['Location' => 'http://www.example.org']);
        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $crawler = new Crawler('MyBot/1.0');

        $crawled = new Crawlable(UriFactory::create('http://www.example.org'));
        $crawler->getCrawledCollection()->add($crawled);

        $this->assertEmpty($crawler->getCrawlingQueue());
        $observer->fulfilled($response, $crawlable, $crawler);
        $this->assertEmpty($crawler->getCrawlingQueue());
    }
}
