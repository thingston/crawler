<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Observer;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\UriFactory;

/**
 * Links observer.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class LinksObserver extends NullObserver
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
        $logger = $crawler->getLogger();

        if ($crawler->getDepth() <= $crawlable->getDepth()) {
            $logger->debug('Max depth reached.', [
                'uri' => (string) $crawlable->getUri(),
                'depth' => $crawlable->getDepth(),
            ]);

            return;
        }

        try {
            $body = $response->getBody()->getContents();
            $dom = new DomCrawler($body, $crawlable->getUri());
        } catch (Exception $e) {
            $logger->info('No DOM present; links extraction ignored.', [
                'uri' => (string) $crawlable->getUri(),
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $profiler = $crawler->getProfiler();
        $crawling = $crawler->getCrawlingQueue();
        $crawled = $crawler->getCrawledCollection();

        $base = $crawlable->getUri();

        $children = [];

        /* @var $a \DOMElement */
        foreach ($dom->filter('a') as $a) {
            if (false === $a->hasAttribute('href')) {
                continue;
            }

            $uri = UriFactory::absolutify($a->getAttribute('href'), $base);
            $subset = [new Crawlable($uri, $crawlable)];

            if ('' !== $uri->getFragment()) {
                array_unshift($subset, new Crawlable($uri->withFragment(''), $crawlable));
            }

            if ('' !== $uri->getQuery()) {
                array_unshift($subset, new Crawlable($uri->withQuery('')->withFragment(''), $crawlable));
            }

            foreach ($subset as $child) {
                if (false === $profiler->crawl($child)) {
                    continue;
                }

                $key = $child->getKey();

                if (true === $crawled->has($key)) {
                    continue;
                }

                $children[$key] = $child;
            }
        }

        foreach ($children as $child) {
            $crawling->enqueue($child);
            $logger->debug(Crawler::LOG_URI_ADDED, ['uri' => (string) $child->getUri()]);
        }
    }
}
