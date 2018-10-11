<?php

/**
 * Thingston Crawler
 *
 * @version 0.3.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Storage;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Storage interface.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
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
