<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler\Crawlable;

use DateTime;
use PHPUnit\Framework\TestCase;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableHydrator;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawlable\CrawlableProxy;
use Thingston\Crawler\Storage\ArrayStorage;
use Thingston\Crawler\UriFactory;

/**
 * Crawlable hydrator test case.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class CrawlableHydratorTest extends TestCase
{

    private $storage;

    protected function setUp()
    {
        $this->storage = new ArrayStorage();
        parent::setUp();
    }

    public function testHydrateExtraction()
    {
        $crawlable = new Crawlable(UriFactory::create('http://example.org/path/to/page.html'));
        $canonical = new Crawlable(UriFactory::create('http://example.org/path/to/'));
        $parent = new Crawlable(UriFactory::create('http://example.org/'));

        $this->storage->offsetSet($canonical->getKey(), $canonical);
        $this->storage->offsetSet($parent->getKey(), $parent);

        $start = microtime(true) - rand(1000, 9999) / 1000;
        $duration = rand(100, 999) / 1000;
        $crawled = new DateTime(date('c', $start + $duration));

        $data = [
            'uri' => $crawlable->getUri(),
            'key' => $crawlable->getKey(),
            'parent' => $parent->getKey(),
            'canonical' => $canonical->getKey(),
            'start' => $start,
            'periodicity' => 2,
            'priority' => 10,
            'duration' => $duration,
            'crawled' => $crawled->format('c'),
            'modified' => null,
            'mime_type' => 'text/html',
            'status' => 200,
            'headers' => serialize(['Content-Type' => ['text/html']]),
            'body' => 'some sort of html',
            'metadata' => null,
        ];

        $hydrator = new CrawlableHydrator($this->storage);

        $hydrated = $hydrator->hydrate($data);

        foreach (array_keys($data) as $property) {
            $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));

            if (true === in_array($property, ['canonical', 'parent'])) {
                $this->assertInstanceOf(CrawlableInterface::class, $hydrated->$method());
                $this->assertEquals($data[$property], $hydrated->$method()->getKey());
            } elseif (true === in_array($property, ['crawled'])) {
                $this->assertInstanceOf(DateTime::class, $hydrated->$method());
            } elseif (true === in_array($property, ['headers'])) {
                $this->assertTrue(is_array($hydrated->$method()));
            } elseif (null === $data[$property]) {
                $this->assertNull($hydrated->$method());
            } else {
                $this->assertEquals($data[$property], $hydrated->$method());
            }
        }

        $extracted = $hydrator->extract($hydrated);

        foreach (array_keys($data) as $property) {
            $this->assertSame($data[$property], $extracted[$property], $property);
        }
    }
}
