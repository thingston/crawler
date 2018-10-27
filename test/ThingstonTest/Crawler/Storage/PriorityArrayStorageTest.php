<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Storage;

use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Storage\PriorityArrayStorage;
use Thingston\Crawler\UriFactory;

class PriorityArrayStorageTest extends TestCase
{

    public function testArrayAccess()
    {
        $storage = new PriorityArrayStorage();
        $this->assertCount(0, $storage);

        $crawlable = new Crawlable(UriFactory::create('http://example.org'));
        $key = $crawlable->getKey();

        $storage[$key] = $crawlable;

        $this->assertCount(1, $storage);
        $this->assertTrue(isset($storage[$key]));
        $this->assertSame($crawlable, $storage[$key]);

        unset($storage[$key]);
        $this->assertCount(0, $storage);
        $this->assertFalse(isset($storage[$key]));
    }
}
