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

use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\UriFactory;

/**
 * Sitemap observer.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class SitemapObserver extends NullObserver
{

    /**
     * Sitemap max length
     */
    const MAX_LENGTH = 52428800; // 50MB

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

        $isXml = $this->isXml($response);
        $isGzip = $this->isGzip($response);

        if (false === $isXml && false === $isGzip) {
            return;
        }

        $body = $crawlable->getBody()->getContents();
        $logger = $crawler->getLogger();

        if (true === $isGzip) {
            if (false === $body = gzdecode($body, self::MAX_LENGTH)) {
                $logger->notice('Unable to decode gzipped XML.', ['uri' => $crawlable->getUri()]);
                return;
            }
        }

        try {
            $xml = new SimpleXMLElement($body);
        } catch (Exception $e) {
            $logger->notice($e->getMessage(), ['uri' => $crawlable->getUri()]);
            return;
        }

        if (false === in_array($xml->getName(), ['urlset', 'sitemapindex'])) {
            return;
        }

        $crawlables = [];
        $isIndex = 'sitemapindex' === $xml->getName();
        $node = true === $isIndex ? 'sitemap' : 'url';

        foreach ($xml->$node as $entry) {
            if (false === isset($entry->loc)) {
                continue;
            }

            $newCrawlable = new Crawlable(UriFactory::create($entry->loc));

            if (true === isset($entry->lastmod)) {
                $crawlable->setModified(new DateTime($entry->lastmod));
            }

            if (true === $isIndex) {
                $crawlable->setPriority(Crawler::PRIORITY_HIGHEST);
            }

            $crawlables[] = $newCrawlable;
        }

        $this->enqueue($crawlables, $crawler);
    }
}
