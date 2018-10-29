<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use DateTime;
use DateTimeInterface;
use Psr\Http\Message\UriInterface;
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
    protected $uri;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var CrawlableInterface
     */
    protected $parent;

    /**
     * @var CrawlableInterface
     */
    protected $canonical;

    /**
     * @var float
     */
    protected $start;

    /**
     * @var int
     */
    protected $periodicity = self::PERIODICITY_HOURLY;

    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * @var duration
     */
    protected $duration;

    /**
     * @var DateTimeInterface
     */
    protected $crawled;

    /**
     * @var DateTimeInterface
     */
    protected $modified;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var array
     */
    protected $metadata;

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
    }

    /**
     * Create new crawlable instance from a string uri.
     *
     * @param string $uri
     * @return CrawlableInterface
     */
    public static function create(string $uri): CrawlableInterface
    {
        return new Crawlable(UriFactory::create($uri));
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
     * Set canonical.
     *
     * @param CrawlableInterface $canonical
     * @return CrawlableInterface
     */
    public function setCanonical(CrawlableInterface $canonical): CrawlableInterface
    {
        $this->canonical = $canonical;

        return $this;
    }

    /**
     * Get canonical Crawlable.
     *
     * @return \Thingston\Crawler\CrawlableInterface|null
     */
    public function getCanonical(): ?CrawlableInterface
    {
        return $this->canonical;
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
     * Set periodicity.
     *
     * @param int $periodicity
     * @return CrawlableInterface
     */
    public function setPeriodicity(int $periodicity): CrawlableInterface
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    /**
     * Get periodicity.
     *
     * @return int
     */
    public function getPeriodicity(): int
    {
        return $this->periodicity;
    }

    /**
     * Check either periodicity is passed.
     *
     * @return bool
     */
    public function isPeriodicity(): bool
    {
        if (null === $crawled = $this->getCrawled()) {
            return true;
        }

        switch ($this->periodicity) {
            case self::PERIODICITY_NEVER:
                return false;
            case self::PERIODICITY_YEARLY:
                $interval = 365 * 24 * 3600;
                break;
            case self::PERIODICITY_MONTHLY:
                $interval = 30 * 24 * 3600;
                break;
            case self::PERIODICITY_WEEKLY:
                $interval = 7 * 24 * 3600;
                break;
            case self::PERIODICITY_DAILY:
                $interval = 24 * 3600;
                break;
            case self::PERIODICITY_HOURLY:
                $interval = 3600;
                break;
            default:
                return true;
        }

        $period = $crawled->getTimestamp() + $interval;

        return time() > $period;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     * @return CrawlableInterface
     */
    public function setPriority(int $priority): CrawlableInterface
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
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
     * Set last modified datetime.
     *
     * @param DateTimeInterface $modified
     * @return CrawlableInterface
     */
    public function setModified(DateTimeInterface $modified): CrawlableInterface
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get last modified datetime.
     *
     * @return DateTimeInterface|null
     */
    public function getModified(): ?DateTimeInterface
    {
        return $this->modified;
    }

    /**
     * Check a given date agains last modified.
     *
     * @param DateTimeInterface $since
     * @return bool
     */
    public function isModified(DateTimeInterface $since): bool
    {
        return null === $this->modified || $this->modified->getTimestamp() < $since->getTimestamp();
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
     * Set MIME type.
     *
     * @param string $mimeType
     * @return CrawlableInterface
     */
    public function setMimeType(string $mimeType): CrawlableInterface
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get MIME type.
     *
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
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
     * @param string $body
     * @return CrawlableInterface
     */
    public function setBody(string $body): CrawlableInterface
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get response body.
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Get content length.
     *
     * @return int
     */
    public function getLength(): int
    {
        return strlen($this->body);
    }

    /**
     * Set metadata.
     *
     * @param array $metadata
     * @return CrawlableInterface
     */
    public function setMetadata(array $metadata): CrawlableInterface
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata.
     *
     * @return array|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->uri;
    }
}
