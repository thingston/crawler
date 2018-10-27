<?php

/**
 * Thingston Crawler
 *
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
     * @var bool
     */
    private $withQuery;

    /**
     * @var bool
     */
    private $withFragment;

    /**
     * Create new instance.
     *
     * @param bool $withQuery
     * @param bool $withFragment
     */
    public function __construct(bool $withQuery = true, bool $withFragment = false)
    {
        $this->withQuery = $withQuery;
        $this->withFragment = $withFragment;
    }

    /**
     * Process a fulfilled request.
     *
     * @param ResponseInterface $response
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function fulfilled(ResponseInterface $response, CrawlableInterface $crawlable, Crawler $crawler)
    {
        if (false === $this->isHtml($response)) {
            return;
        }

        $logger = $crawler->getLogger();

        if ($crawler->getDepth() <= $crawlable->getDepth()) {
            $logger->debug('Max depth reached.', [
                'uri' => (string) $crawlable->getUri(),
                'depth' => $crawlable->getDepth(),
            ]);

            return;
        }

        try {
            $body = $crawlable->getBody();
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

        /* @var $tag \DOMElement */
        foreach ($dom->filter('link') as $tag) {
            if (false === $tag->hasAttribute('rel') || false === $tag->hasAttribute('href') || 'canonical' !== $tag->getAttribute('rel')) {
                continue;
            }

            try {
                $uri = UriFactory::absolutify($tag->getAttribute('href'), $base);
            } catch (Exception $e) {
                continue;
            }

            $canonical = (new Crawlable($uri))->setPriority(Crawler::PRIORITY_HIGH);
            $key = $canonical->getKey();

            if (true === $crawled->has($key)) {
                $canonical = $crawled->get($key);
            }

            $crawlable->setCanonical($canonical);
            $crawling->enqueue($crawlable);
        }

        foreach ($dom->filter('a') as $tag) {
            if (false === $tag->hasAttribute('href')) {
                continue;
            }

            try {
                $uri = UriFactory::absolutify($tag->getAttribute('href'), $base);
            } catch (Exception $e) {
                continue;
            }

            if (false === $this->withQuery) {
                $uri = $uri->withQuery('');
            }

            if (false === $this->withFragment) {
                $uri = $uri->withFragment('');
            }

            $subset = [new Crawlable($uri, $crawlable)];

            if (true === $this->withQuery && '' !== $uri->getQuery()) {
                $subset[0]->setPriority(Crawler::PRIORITY_LOW);
                array_push($subset, new Crawlable($uri->withQuery('')->withFragment(''), $crawlable));
            }

            if (true === $this->withFragment && '' !== $uri->getFragment()) {
                $subset[0]->setPriority(Crawler::PRIORITY_LOWEST);
                array_push($subset, new Crawlable($uri->withFragment(''), $crawlable));
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
