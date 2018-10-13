<?php

/**
 * Thingston Crawler
 *
 * @version 0.3.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Observer;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;

/**
 * Observer interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
interface ObserverInterface
{

    /**
     * Process a request before being sent.
     *
     * @param RequestInterface $request
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function request(RequestInterface $request, CrawlableInterface $crawlable, Crawler $crawler);

    /**
     * Process a fulfilled request.
     *
     * @param ResponseInterface $response
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function fulfilled(ResponseInterface $response, CrawlableInterface $crawlable, Crawler $crawler);

    /**
     * Process a rejected request.
     *
     * @param Exception $reason
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function rejected(Exception $reason, CrawlableInterface $crawlable, Crawler $crawler);
}
