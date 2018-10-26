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

use GuzzleHttp\Psr7\UriResolver;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Zend\Feed\Reader\Reader;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\UriFactory;

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

            $target = new Crawlable(UriResolver::resolve($base, new Uri($link)), $crawlable);
            $target->setPriority(self::FEED_PRIORITY);

            if (null !== $dateCreated = $entry->getDateCreated()) {
                $target->setModified($dateModified);
            } elseif (null !== $dateModified = $entry->getDateModified()) {
                $target->setModified($dateCreated);
            }

            $crawlables[$target->getKey()] = $target;
        }

        $this->enqueue($crawlables, $crawler);
    }
}
