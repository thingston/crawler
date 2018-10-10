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

use DateTimeInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Crawlable entity interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
interface CrawlableInterface
{

    /**
     * Get URI.
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface;

    /**
     * Get unique key.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Get parent Crawlable.
     *
     * @return \Thingston\Crawler\CrawlableInterface|null
     */
    public function getParent(): ?CrawlableInterface;

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth(): int;

    /**
     * Set start microtime.
     *
     * @param float $start
     * @return CrawlableInterface
     */
    public function setStart(float $start): CrawlableInterface;

    /**
     * Get start microtime.
     *
     * @return float|null
     */
    public function getStart(): ?float;

    /**
     * Set duration of request in seconds.
     *
     * @param float $duration
     * @return CrawlableInterface
     */
    public function setDuration(float $duration): CrawlableInterface;

    /**
     * Get duration of request in seconds.
     *
     * @return float|null
     */
    public function getDuration(): ?float;

    /**
     * Set datetime when this was crawled.
     *
     * @param DateTimeInterface $crawled
     * @return CrawlableInterface
     */
    public function setCrawled(DateTimeInterface $crawled): CrawlableInterface;

    /**
     * Get datetime when this was crawled.
     *
     * @return DateTimeInterface|null
     */
    public function getCrawled(): ?DateTimeInterface;

    /**
     * Set latest status code.
     *
     * @param int $status
     * @return CrawlableInterface
     */
    public function setStatus(int $status): CrawlableInterface;

    /**
     * Get latest status code.
     *
     * @return int
     */
    public function getStatus(): ?int;

    /**
     * Set HTTPS headers.
     *
     * @param array $headers
     * @return CrawlableInterface
     */
    public function setHeaders(array $headers): CrawlableInterface;

    /**
     * Get HTTP headers.
     *
     * @return array|null
     */
    public function getHeaders(): ?array;

    /**
     * Set response body.
     *
     * @param StreamInterface $body
     * @return CrawlableInterface
     */
    public function setBody(StreamInterface $body): CrawlableInterface;

    /**
     * Get response body.
     *
     * @return StreamInterface|null
     */
    public function getBody(): ?StreamInterface;
}
