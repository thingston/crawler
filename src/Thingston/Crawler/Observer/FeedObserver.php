<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Observer;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Zend\Feed\Reader\Reader;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;

/**
 * Feed observer.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class FeedObserver extends NullObserver
{

    const FEED_PRIORITY = 35;

    /**
     * Process a fulfilled request.
     *
     * @param ResponseInterface $response
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function fulfilled(ResponseInterface $response, CrawlableInterface $crawlable, Crawler $crawler)
    {
        if (false === $this->isFeed($response)) {
            return;
        }

        $logger = $crawler->getLogger();

        try {
            $reader = Reader::importString($crawlable->getBody());
        } catch (Exception $e) {
            $logger->notice($e->getMessage(), ['uri' => (string) $crawlable->getUri()]);
            return;
        }

        $reader->setOriginalSourceUri((string) $crawlable->getUri());

        $base = $crawlable->getUri();
        $crawlables = [];

        foreach ($reader as $entry) {
            if (null === $link = $entry->getLink()) {
                continue;
            }

            $item = new Crawlable(UriResolver::resolve($base, new Uri($link)), $crawlable);
            $item->setPriority(self::FEED_PRIORITY);

            if (null !== $dateModified = $entry->getDateModified()) {
                $item->setModified($dateModified);
            } elseif (null !== $dateCreated = $entry->getDateCreated()) {
                $item->setModified($dateCreated);
            }

            $crawlables[$item->getKey()] = $item;
        }

        $this->enqueue($crawlables, $crawler);
    }
}
