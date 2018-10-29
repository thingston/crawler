<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Crawlable;

use DateTimeInterface;
use Psr\Http\Message\UriInterface;
use Thingston\Crawler\Storage\StorageAwareInterface;
use Thingston\Crawler\Storage\StorageAwareTrait;
use Thingston\Crawler\Storage\StorageInterface;

/**
 * Crawlable proxy.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class CrawlableProxy extends Crawlable implements CrawlableInterface, StorageAwareInterface
{

    use StorageAwareTrait;

    /**
     * @var bool
     */
    private $loaded;

    /**
     * Create new instance.
     *
     * @param StorageInterface $storage
     * @param string $key
     */
    public function __construct(StorageInterface $storage, string $key)
    {
        $this->storage = $storage;
        $this->key = $key;
        $this->loaded = false;
    }

    /**
     * Load from storage.
     *
     * @return CrawlableInterface
     */
    public function load(): CrawlableInterface
    {
        if (true === $this->isLoaded()) {
            return $this;
        }

        /* @var $stored CrawlableInterface */
        if (null === $stored = $this->storage[$this->key]) {
            $this->storage[$this->key] = $this;
        } else {
            $this->uri = $stored->getUri();
            $this->parent = $stored->getParent();
            $this->canonical = $stored->getCanonical();
            $this->start = $stored->getStart();
            $this->periodicity = $stored->getPeriodicity();
            $this->priority = $stored->getPriority();
            $this->crawled = $stored->getCrawled();
            $this->modified = $stored->getModified();
            $this->mimeType = $stored->getMimeType();
            $this->status = $stored->getStatus();
            $this->headers = $stored->getHeaders();
            $this->body = $stored->getBody();
            $this->metadata = $stored->getMetadata();
        }

        $this->loaded = true;

        return $this;
    }

    /**
     * Check crawlable is loaded.
     *
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Get URI.
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        $this->load();

        return parent::getUri();
    }

    /**
     * Get parent Crawlable.
     *
     * @return \Thingston\Crawler\CrawlableInterface|null
     */
    public function getCanonical(): ?CrawlableInterface
    {
        $this->load();

        return parent::getCanonical();
    }

    /**
     * Get parent Crawlable.
     *
     * @return \Thingston\Crawler\CrawlableInterface|null
     */
    public function getParent(): ?CrawlableInterface
    {
        $this->load();

        return parent::getParent();
    }

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth(): int
    {
        $this->load();

        return parent::getDepth();
    }

    /**
     * Get periodicity.
     *
     * @return int
     */
    public function getPeriodicity(): int
    {
        $this->load();

        return parent::getPeriodicity();
    }

    /**
     * Check either periodicity is passed.
     *
     * @return bool
     */
    public function isPeriodicity(): bool
    {
        $this->load();

        return parent::isPeriodicity();
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        $this->load();

        return parent::getPriority();
    }

    /**
     * Get start microtime.
     *
     * @return float|null
     */
    public function getStart(): ?float
    {
        $this->load();

        return parent::getStart();
    }

    /**
     * Get duration of request in seconds.
     *
     * @return float|null
     */
    public function getDuration(): ?float
    {
        $this->load();

        return parent::getDuration();
    }

    /**
     * Get datetime when this was crawled.
     *
     * @return DateTimeInterface|null
     */
    public function getCrawled(): ?DateTimeInterface
    {
        $this->load();

        return parent::getCrawled();
    }

    /**
     * Get last modified datetime.
     *
     * @return DateTimeInterface|null
     */
    public function getModified(): ?DateTimeInterface
    {
        $this->load();

        return parent::getModified();
    }

    /**
     * Check a given date agains last modified.
     *
     * @param DateTimeInterface $since
     * @return bool
     */
    public function isModified(DateTimeInterface $since): bool
    {
        $this->load();

        return parent::isModified();
    }

    /**
     * Get latest status code.
     *
     * @return int
     */
    public function getStatus(): ?int
    {
        $this->load();

        return parent::getStatus();
    }

    /**
     * Get MIME type.
     *
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        $this->load();

        return parent::getMimeType();
    }

    /**
     * Get HTTP headers.
     *
     * @return array|null
     */
    public function getHeaders(): ?array
    {
        $this->load();

        return parent::getHeaders();
    }

    /**
     * Get response body.
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        $this->load();

        return parent::getBody();
    }

    /**
     * Get content length.
     *
     * @return int
     */
    public function getLength(): int
    {
        $this->load();

        return parent::getLength();
    }

    /**
     * Get metadata.
     *
     * @return array|null
     */
    public function getMetadata(): ?array
    {
        $this->load();

        return parent::getMetadata();
    }
}
