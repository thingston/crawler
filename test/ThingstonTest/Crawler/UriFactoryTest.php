<?php

/**
 * Thingston Crawler
 *
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
            [md5('http://localhost/'), 'http://localhost/'],
            [md5('http://localhost/?a=1&b=2'), 'http://localhost/?a=1&b=2'],
            [md5('http://localhost/?a=1&b=2'), 'http://localhost/?b=2&a=1'],
        ];
    }

    public function testRobotify()
    {
        $uri = 'http://example.org/path/file.html?a=1#foo';
        $this->assertEquals('http://example.org/robots.txt', UriFactory::robotify($uri));
    }
}
