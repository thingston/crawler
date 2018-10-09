<?php

namespace Thingston\Crawler\Storage;

trait StorageAwareTrait
{

    /**
     * @var \Thingston\Crawler\Storage\StorageInterface
     */
    private $storage;

    /**
     * Set storage.
     *
     * @param Storage\StorageInterface $storage
     * @return StorageAwareInterface
     */
    public function setStorage(StorageInterface $storage): StorageAwareInterface
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get storage.
     *
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Clear the storage by removing all elements.
     *
     * @return StorageAwareInterface
     */
    public function clear(): StorageAwareInterface
    {
        $this->storage->clear();

        return $this;
    }

    /**
     * Check either storage is empty..
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->storage->isEmpty();
    }
}
