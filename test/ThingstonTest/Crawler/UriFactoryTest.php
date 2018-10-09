<?php

/**
 * Thingston Crawler
 *
 * @version 0.1.0
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace ThingstonTest\Crawler;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Thingston\Crawler\UriFactory;

class UriFactoryTest extends TestCase
{

    /**
     * @dataProvider createProvider
     */
    public function testCreate($uri)
    {
        $this->assertInstanceOf(UriInterface::class, UriFactory::create($uri));

        if ($uri instanceof UriInterface) {
            $this->assertSame($uri, UriFactory::create($uri));
        }
    }

    public function createProvider(): array
    {
        return [
            [''],
            ['/'],
            ['?a=1'],
            ['#foo'],
            ['localhost:8080'],
            ['http://localhost:8080'],
            [new Uri('http://localhost:8080')],
        ];
    }

    /**
     * @dataProvider absolutifyProvider
     */
    public function testAbsolutify($expected, $uri, $base)
    {
        $this->assertEquals($expected, UriFactory::absolutify($uri, $base));
    }

    public function absolutifyProvider(): array
    {
        $base = new Uri('http://example.org:8080/path?a=1#foo');

        return [
            [$base, $base, $base],
            [$base, '', $base],
            [$base, '//', $base],
            [new Uri('http://example.net'), '//example.net', $base],
            [new Uri('http://example.org:8080/'), '/', $base],
        ];
    }

    /**
     * @dataProvider hashProvider
     */
    public function testHash($expected, $uri)
    {
        $this->assertEquals($expected, UriFactory::hash($uri));
    }

    public function hashProvider(): array
    {
        return [
            [UriFactory::hash(''), ''],
            [md5('localhost/'), 'http://localhost/'],
            [md5('localhost/'), 'https://localhost/'],
            [md5('localhost/?a=1&b=2'), 'https://localhost/?a=1&b=2'],
            [md5('localhost/?a=1&b=2'), 'http://localhost/?b=2&a=1'],
        ];
    }
}
