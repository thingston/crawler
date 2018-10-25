<?php

/**
 * Thingston Crawler
 *
 * @version 0.4.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use DateTimeInterface;
use Psr\Http\Message\UriInterface;

/**
 * Crawlable entity interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
interface CrawlableInterface
{

    /**
     * Periodicityy codes
     */
    const PERIODICITY_ALWAYS = 0;
    const PERIODICITY_HOURLY = 1;
    const PERIODICITY_DAILY = 2;
    const PERIODICITY_WEEKLY = 3;
    const PERIODICITY_MONTHLY = 4;
    const PERIODICITY_YEARLY = 5;
    const PERIODICITY_NEVER = 6;

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
     * Set periodicity.
     *
     * @param int $periodicity
     * @return CrawlableInterface
     */
    public function setPeriodicity(int $periodicity): CrawlableInterface;

    /**
     * Get periodicity.
     *
     * @return int
     */
    public function getPeriodicity(): int;

    /**
     * Check either periodicity is passed.
     *
     * @return bool
     */
    public function isPeriodicity(): bool;

    /**
     * Set priority.
     *
     * @param int $priority
     * @return CrawlableInterface
     */
    public function setPriority(int $priority): CrawlableInterface;

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int;

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
     * Set last modified datetime.
     *
     * @param DateTimeInterface $modified
     * @return CrawlableInterface
     */
    public function setModified(DateTimeInterface $modified): CrawlableInterface;

    /**
     * Get last modified datetime.
     *
     * @return DateTimeInterface|null
     */
    public function getModified(): ?DateTimeInterface;

    /**
     * Check a given date agains last modified.
     *
     * @param DateTimeInterface $since
     * @return bool
     */
    public function isModified(DateTimeInterface $since): bool;

    /**
     * Set MIME type.
     *
     * @param string $mimeType
     * @return CrawlableInterface
     */
    public function setMimeType(string $mimeType): CrawlableInterface;

    /**
     * Get MIME type.
     *
     * @return string|null
     */
    public function getMimeType(): ?string;

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
     * @param string $body
     * @return CrawlableInterface
     */
    public function setBody(string $body): CrawlableInterface;

    /**
     * Get response body.
     *
     * @return string|null
     */
    public function getBody(): ?string;

    /**
     * Get content length.
     *
     * @return int
     */
    public function getLength(): int;

    /**
     * Set metadata.
     *
     * @param array $metadata
     * @return CrawlableInterface
     */
    public function setMetadata(array $metadata): CrawlableInterface;


    /**
     * Get metadata.
     *
     * @return array|null
     */
    public function getMetadata(): ?array;
}
