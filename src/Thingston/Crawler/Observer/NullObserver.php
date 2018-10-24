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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;

/**
 * Crawlable queue interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class NullObserver implements ObserverInterface
{

    /**
     * Process a request before being sent.
     *
     * @param RequestInterface $request
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function request(RequestInterface $request, CrawlableInterface $crawlable, Crawler $crawler)
    {
        // nothing to do
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
        // nothing to do
    }

    /**
     * Process a rejected request.
     *
     * @param Exception $reason
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function rejected(Exception $reason, CrawlableInterface $crawlable, Crawler $crawler)
    {
        // nothing to do
    }

    /**
     * Check the response body is empty.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function isEmptyBody(ResponseInterface $response): bool
    {
        if (null === $response->getBody()) {
            return true;
        }

        if (true === empty($response->getBody()->getContents())) {
            return true;
        }

        $response->getBody()->rewind();

        return false;
    }

    /**
     * Get response content-type.
     *
     * @param ResponseInterface $response
     * @return string
     */
    public function getContentType(ResponseInterface $response): string
    {
        if (false === $response->hasHeader('Content-Type') && null !== $response->getBody()) {
            $response->getBody()->rewind();
            $body = $response->getBody()->getContents();
            $path = sys_get_temp_dir() . '/' . md5($body);
            file_put_contents($path, $body);
            $types[] = mime_content_type($path);
        } else {
            $types = $response->getHeader('Content-Type');
        }

        foreach ($types as $type) {
            if (false !== $pos = strpos($type, ';')) {
                $type = substr($type, 0, $pos);
            }

            return $type;
        }

        return 'text/plain';
    }

    /**
     * Check response has one of given content-types.
     *
     * @param ResponseInterface $response
     * @param array $types
     * @return bool
     */
    public function hasContentTypes(ResponseInterface $response, array $types): bool
    {
         return in_array($this->getContentType($response), $types);
    }

    /**
     * Enqueue an array of Crawlable instances.
     *
     * @param array $crawlables
     * @param Crawler $crawler
     */
    public function enqueue(array $crawlables, Crawler $crawler)
    {
        $profiler = $crawler->getProfiler();
        $crawling = $crawler->getCrawlingQueue();
        $crawled = $crawler->getCrawledCollection();

        /* @var $crawlable CrawlableInterface */
        foreach ($crawlables as $crawlable) {
            if (false === $profiler->crawl($crawlable)) {
                continue;
            }

            $key = $crawlable->getKey();

            if (true === $crawled->has($key)) {
                $latest = $crawled->get($key);
                $since = $crawlable->getModified();

                if (null !== $since && false === $latest->isModified($since)) {
                    continue;
                }

                if (false === $latest->isPeriodicity()) {
                    continue;
                }
            }

            $crawling->enqueue($crawlable);
        }
    }

    /**
     * Check response is an HTML file.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function isHtml(ResponseInterface $response): bool
    {
        return $this->hasContentTypes($response, ['text/html', 'text/x-server-parsed-html']);
    }

    /**
     * Check response is an XML file.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function isXml(ResponseInterface $response): bool
    {
        return $this->hasContentTypes($response, ['text/xml', 'application/xml']);
    }

    /**
     * Check response is a GZIP file.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function isGzip(ResponseInterface $response): bool
    {
        return $this->hasContentTypes($response, ['application/x-gzip', 'application/gzip', 'application/zlib']);
    }
}
