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
use Thingston\Crawler\Crawlable\CrawlableHydrator;
use Thingston\Crawler\Crawlable\CrawlableHydratorInterface;
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
     * @var CrawlableHydratorInterface
     */
    protected $hydrator;

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
     * @param CrawlableHydratorInterface $hydrator
     */
    public function __construct(Connection $connection, FilesystemInterface $filesystem, string $table = 'crawlables', CrawlableHydratorInterface $hydrator = null)
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->table = $table;
        $this->hydrator = $hydrator;
    }

    /**
     * Set hydrator.
     *
     * @param \HydratorInterface $hydrator
     * @return StorageInterface
     */
    public function setHydrator(HydratorInterface $hydrator): StorageInterface
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Get hydrator.
     *
     * @return CrawlableHydratorInterface
     */
    public function getHydrator(): CrawlableHydratorInterface
    {
        if (null === $this->hydrator) {
            $this->hydrator = new CrawlableHydrator($this);
        }

        return $this->hydrator;
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
        $qb = $this->connection->createQueryBuilder();

        $sql = $qb->select('COUNT(*)')
                ->from($this->table)
                ->getSQL();

        $result = $this->connection->fetchArray($sql);

        return 0 === (int) $result[0];
    }

    /**
     * Whether an offset exists
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        $qb = $this->connection->createQueryBuilder();

        $sql = $qb->select('COUNT(*)')
                ->from($this->table)
                ->where('`key` = :key')
                ->getSQL();

        $params = ['key' => $key];
        $result = $this->connection->fetchArray($sql, $params);

        return 1 === (int) $result[0];
    }

    /**
     * Offset to retrieve
     *
     * @param string $key
     * @return CrawlableInterface|null
     */
    public function offsetGet($key)
    {
        if (false === $this->offsetExists($key)) {
            return null;
        }

        $cn = $this->connection;
        $qb = $cn->createQueryBuilder();

        $sql = $qb->select('*')
                ->from($this->table)
                ->where($cn->quoteIdentifier('key') . ' = :key')
                ->getSQL();

        $params = ['key' => $key];
        $result = $this->connection->fetchAssoc($sql, $params);

        $crawlable = $this->getHydrator()->hydrate($result);

        return $crawlable;
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

        $cn = $this->connection;
        $qb = $cn->createQueryBuilder();

        $data = $this->getHydrator()->extract($crawlable);
        $values = [];

        foreach ($data as $name => $value) {
            switch (strtolower(gettype($value))) {
                case 'null' :
                    $type = \PDO::PARAM_NULL;
                    break;
                case 'boolean' :
                    $type = \PDO::PARAM_BOOL;
                    break;
                case 'integer' :
                    $type = \PDO::PARAM_INT;
                    break;
                case 'string' :
                case 'double' :
                    $type = \PDO::PARAM_INT;
                    break;
                case 'array' :
                case 'object' :
                    $type = \PDO::PARAM_INT;
                    $value = serialize($value);
                    break;
                default :
                    continue;
            }

            $identifier = $cn->quoteIdentifier($name);
            $values[$identifier] = $qb->createNamedParameter($value, $type, ":$name");
        }

        if (true === $this->offsetExists($key)) {
            $sql = $qb->update($this->table);

            foreach ($values as $identifier => $placeholder) {
                $sql->set($identifier, $placeholder);
            }
        } else {
            $sql = $qb->insert($this->table)->values($values);
        }

        $cn->executeQuery($sql->getSQL(), $data);
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
        $qb = $this->connection->createQueryBuilder();

        $sql = $qb->select('COUNT(*)')
                ->from($this->table)
                ->getSQL();

        $result = $this->connection->fetchArray($sql);

        return (int) $result[0];
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
