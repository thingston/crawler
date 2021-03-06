<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Observer;

use Psr\Http\Message\ResponseInterface;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\UriFactory;

/**
 * Redirection observer.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class RedirectionObserver extends NullObserver
{

    /**
     * Process a fulfilled request.
     *
     * @param ResponseInterface $response
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function fulfilled(ResponseInterface $response, CrawlableInterface $crawlable, Crawler $crawler)
    {
        $status = $response->getStatusCode();

        if (300 <= $status && 400 > $status) {
            $location = current($response->getHeader('Location'));
            $uri = UriFactory::absolutify($location, $crawlable->getUri());

            if ($uri == $crawlable->getUri()) {
                return;
            }

            $redirection = new Crawlable($uri, $crawlable->getParent());

            if (false === $crawler->getCrawledCollection()->has($redirection->getKey())) {
                $crawler->getCrawlingQueue()->enqueue($redirection);
            }
        }
    }
}
