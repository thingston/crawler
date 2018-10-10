<?php

/**
 * Thingston Crawler
 *
 * @version 0.1.1
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use DateTime;
use DateTimeInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Thingston\Crawler\UriFactory;

/**
 * Crawlable entity.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class Crawlable implements CrawlableInterface
{

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var string
     */
    private $key;

    /**
     * @var CrawlableInterface
     */
    private $parent;

    /**
     * @var float
     */
    private $start;

    /**
     * @var duration
     */
    private $duration;

    /**
     * @var DateTimeInterface
     */
    private $crawled;

    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var StreamInterface
     */
    private $body;

    /**
     * Create new instance.
     *
     * @param UriInterface $uri
     * @param CrawlableInterface $parent
     * @param string $key
     */
    public function __construct(UriInterface $uri, CrawlableInterface $parent = null, string $key = null)
    {
        $this->uri = $uri;
        $this->parent = $parent;
        $this->key = $key ?? UriFactory::hash($uri);
        ;
    }

    /**
     * Get URI.
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Get unique key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get parent Crawlable.
     *
     * @return \Thingston\Crawler\CrawlableInterface|null
     */
    public function getParent(): ?CrawlableInterface
    {
        return $this->parent;
    }

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth(): int
    {
        return null === $this->parent ? 0 : $this->parent->getDepth() + 1;
    }

    /**
     * Set start microtime.
     *
     * @param float $start
     * @return CrawlableInterface
     */
    public function setStart(float $start): CrawlableInterface
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start microtime.
     *
     * @return float|null
     */
    public function getStart(): ?float
    {
        return $this->start;
    }

    /**
     * Set duration of request in seconds.
     *
     * @param float $duration
     * @return CrawlableInterface
     */
    public function setDuration(float $duration): CrawlableInterface
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration of request in seconds.
     *
     * @return float|null
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * Set datetime when this was crawled.
     *
     * @param DateTimeInterface $crawled
     * @return CrawlableInterface
     */
    public function setCrawled(DateTimeInterface $crawled): CrawlableInterface
    {
        $this->crawled = $crawled;

        return $this;
    }

    /**
     * Get datetime when this was crawled.
     *
     * @return DateTimeInterface|null
     */
    public function getCrawled(): ?DateTimeInterface
    {
        if (null === $this->crawled && null !== $this->start && null !== $this->duration) {
            $date = date('c', $this->start + $this->duration);
            $this->crawled = new DateTime($date);
        }

        return $this->crawled;
    }

    /**
     * Set latest status code.
     *
     * @param int $status
     * @return CrawlableInterface
     */
    public function setStatus(int $status): CrawlableInterface
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get latest status code.
     *
     * @return int
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Set HTTPS headers.
     *
     * @param array $headers
     * @return CrawlableInterface
     */
    public function setHeaders(array $headers): CrawlableInterface
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get HTTP headers.
     *
     * @return array|null
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * Set response body.
     *
     * @param StreamInterface $body
     * @return CrawlableInterface
     */
    public function setBody(StreamInterface $body): CrawlableInterface
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get response body.
     *
     * @return StreamInterface|null
     */
    public function getBody(): ?StreamInterface
    {
        return $this->body;
    }
}
