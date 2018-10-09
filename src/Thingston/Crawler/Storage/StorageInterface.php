<?php

namespace Thingston\Crawler\Storage;

use ArrayAccess;
use Countable;
use Iterator;

interface StorageInterface extends ArrayAccess, Countable, Iterator
{

    /**
     * Clear the storage by removing all elements.
     *
     * @return StorageInterface
     */
    public function clear(): StorageInterface;

    /**
     * Check either storage is empty..
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
