<?php

namespace Thingston\Crawler\Storage;

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
