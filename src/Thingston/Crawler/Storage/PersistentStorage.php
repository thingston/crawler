<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Storage;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use League\Flysystem\FilesystemInterface;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableProxy;
use Thingston\Crawler\Crawlable\CrawlableInterface;

/**
 * Persistent storage.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class PersistentStorage implements StorageInterface
{

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $elements = [];

    /**
     * Create new instance.
     *
     * @param Connection $connection
     * @param FilesystemInterface $filesystem
     * @param string $table
     */
    public function __construct(Connection $connection, FilesystemInterface $filesystem, string $table = 'crawlables')
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->table = $table;
    }

    /**
     * Clear the storage by removing all elements.
     *
     * @return StorageInterface
     */
    public function clear(): StorageInterface
    {
        $this->connection->exec('DELETE FROM ' . $this->table);

        return $this;
    }

    /**
     * Check either storage is empty..
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        $sql = $this->connection->createQueryBuilder()
                ->select('COUNT(*)')
                ->from($this->table)
                ->getSQL();

        return 0 == $this->connection->fetchArray($sql)[0];
    }

    /**
     * Whether an offset exists
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        $sql = $this->connection->createQueryBuilder()
                ->select('COUNT(*)')
                ->from($this->table)
                ->where('key = :key')
                ->getSQL();

        $params = ['key' => $key];

        return 1 == $this->connection->fetchAssoc($sql, $params)[0];
    }

    /**
     * Offset to retrieve
     *
     * @param string $key
     * @return CrawlableInterface|null
     */
    public function offsetGet($key)
    {
        $sql = $this->connection->createQueryBuilder()
                ->select('uri, key, parent')
                ->from($this->table)
                ->where('key = :key')
                ->getSQL();

        $params = ['key' => $key];

        if (false === $row = $this->connection->fetchAssoc($sql, $params)) {
            return null;
        }

        $crawlable;
    }

    /**
     * Assign a value to the specified offset
     *
     * @param string $key
     * @param CrawlableInterface $crawlable
     */
    public function offsetSet($key, $crawlable)
    {
        if (false === $crawlable instanceof CrawlableInterface) {
            throw new InvalidArgumentException(sprintf('Invalid element type; it must be an instance of "%s".', CrawlableInterface::class));
        }

        $this->elements[$key] = $crawlable;
    }

    /**
     * Unset an offset
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->elements[$key]);
    }

    /**
     * Return total number of elements stored.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->elements);
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return false !== current($this->elements);
    }

    /**
     * Return the current element.
     *
     * @return CrawlableInterface
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * Return the key of the current element.
     *
     * @return string
     */
    public function key(): string
    {
        return key($this->elements);
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next()
    {
        next($this->elements);
    }
}
