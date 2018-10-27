<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Storage;

/**
 * Storage aware interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
interface StorageAwareInterface
{

    /**
     * Set storage.
     *
     * @param Storage\StorageInterface $storage
     * @return StorageAwareInterface
     */
    public function setStorage(StorageInterface $storage): StorageAwareInterface;

    /**
     * Get storage.
     *
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * Clear the storage by removing all elements.
     *
     * @return StorageAwareInterface
     */
    public function clear(): StorageAwareInterface;

    /**
     * Check either storage is empty..
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
