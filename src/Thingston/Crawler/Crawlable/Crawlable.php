<?php

namespace Thingston\Crawler\Crawlable;

use DateTimeInterface;
use Psr\Http\Message\UriInterface;
use Thingston\Crawler\UriFactory;

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
     * @var DateTimeInterface
     */
    private $crawled;

    /**
     * @var int
     */
    private $status;

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
}
