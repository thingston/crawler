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
use InvalidArgumentException;
use Thingston\Crawler\Storage\StorageAwareInterface;
use Thingston\Crawler\Storage\StorageAwareTrait;
use Thingston\Crawler\Storage\StorageInterface;
use Thingston\Crawler\UriFactory;

/**
 * Crawlable hydrator.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class CrawlableHydrator implements CrawlableHydratorInterface, StorageAwareInterface
{

    use StorageAwareTrait;

    /**
     * Create new instance.
     *
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Hydrate a crawlable from an array of data.
     *
     * @param array $data
     * @param CrawlableInterface $crawlable
     * @return CrawlableInterface
     */
    public function hydrate(array $data, CrawlableInterface $crawlable = null): CrawlableInterface
    {
        if (false === isset($data['key'])) {
            throw new InvalidArgumentException('At least "key" element must be provided in order to hydrate a Crawlable instance.');
        }

        $uri = true === isset($data['uri']) ? UriFactory::create($data['uri']) : null;
        $key = $data['key'];
        $parent = null;

        if (true === isset($data['parent'])) {
            if ($data['parent'] instanceof CrawlableInterface) {
                $parent = $data['parent'];
            } else {
                $parent = new CrawlableProxy($this->storage, $data['parent']);
            }
        }

        if (null === $crawlable) {
            $crawlable = null !== $uri ? new Crawlable($uri, $parent, $key) : new CrawlableProxy($this->storage, $key);
        }

        if (true === isset($data['canonical'])) {
            if ($data['canonical'] instanceof CrawlableInterface) {
                $crawlable->setCanonical($data['canonical']);
            } else {
                $crawlable->setCanonical(new CrawlableProxy($this->storage, $data['canonical']));
            }
        }

        if (true === isset($data['start'])) {
            $crawlable->setStart($data['start']);
        }

        if (true === isset($data['periodicity'])) {
            $crawlable->setPeriodicity($data['periodicity']);
        }

        if (true === isset($data['priority'])) {
            $crawlable->setPriority($data['priority']);
        }

        if (true === isset($data['duration'])) {
            $crawlable->setDuration($data['duration']);
        }

        if (true === isset($data['crawled'])) {
            if ($data['crawled'] instanceof DateTimeInterface) {
                $crawlable->setCrawled($data['crawled']);
            } else {
                $crawlable->setCrawled(new DateTime($data['crawled']));
            }
        }

        if (true === isset($data['modified'])) {
            if ($data['modified'] instanceof DateTimeInterface) {
                $crawlable->setModified($data['modified']);
            } else {
                $crawlable->setModified(new DateTime($data['modified']));
            }
        }

        if (true === isset($data['mime_type'])) {
            $crawlable->setMimeType($data['mime_type']);
        }

        if (true === isset($data['status'])) {
            $crawlable->setStatus($data['status']);
        }

        if (true === isset($data['headers'])) {
            if (true === is_array($data['headers'])) {
                $crawlable->setHeaders($data['headers']);
            } else {
                $crawlable->setHeaders(unserialize($data['headers']));
            }
        }

        if (true === isset($data['metadata'])) {
            if (true === is_array($data['metadata'])) {
                $crawlable->setMetadata($data['metadata']);
            } else {
                $crawlable->setMetadata(unserialize($data['metadata']));
            }
        }

        return $crawlable;
    }

    /**
     * Extract a crawlable into an array.
     *
     * @param CrawlableInterface $crawlable
     * @return array
     */
    public function extract(CrawlableInterface $crawlable): array
    {
        return [
            'uri' => $crawlable->getUri() ? (string) $crawlable->getUri() : null,
            'key' => $crawlable->getKey(),
            'parent' => $crawlable->getParent() ? $crawlable->getParent()->getKey() : null,
            'canonical' => $crawlable->getCanonical() ? $crawlable->getCanonical()->getKey() : null,
            'start' => $crawlable->getStart(),
            'periodicity' => $crawlable->getPeriodicity(),
            'priority' => $crawlable->getPriority(),
            'duration' => $crawlable->getDuration(),
            'crawled' => $crawlable->getCrawled() ? $crawlable->getCrawled()->format('Y-m-d H:i:s') : null,
            'modified' => $crawlable->getModified() ? $crawlable->getModified()->format('Y-m-d H:i:s') : null,
            'mime_type' => $crawlable->getMimeType(),
            'status' => $crawlable->getStatus(),
            'headers' => $crawlable->getHeaders() ? serialize($crawlable->getHeaders()) : null,
            'metadata' => $crawlable->getMetadata() ? serialize($crawlable->getMetadata()) : null,
        ];
    }
}
