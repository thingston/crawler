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
use RobotsTxtParser;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\UriFactory;

/**
 * Robots observer.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class RobotsObserver extends NullObserver
{

    const PRIORITY_SITEMAP = 50;

    /**
     * Process a fulfilled request.
     *
     * @param ResponseInterface $response
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function fulfilled(ResponseInterface $response, CrawlableInterface $crawlable, Crawler $crawler)
    {
        if (true === $this->isEmptyBody($response)) {
            return;
        }

        if ('/robots.txt' !== $crawlable->getUri()->getPath()) {
            return;
        }

        $robots = new RobotsTxtParser($crawlable->getBody());
        $crawlables = [];

        foreach ($robots->getSitemaps() as $sitemap) {
            $crawlables[] = (new Crawlable(UriFactory::create($sitemap), $crawlable))
                    ->setPriority(Crawler::PRIORITY_HIGHEST);
        }

        $this->enqueue($crawlables, $crawler);
    }
}
