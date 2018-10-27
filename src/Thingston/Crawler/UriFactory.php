<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\UriInterface;

/**
 * Uri Factory.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class UriFactory
{

    /**
     * Create new URI object.
     *
     * @param UriInterface|string $uri
     * @return UriInterface
     */
    public static function create($uri): UriInterface
    {
        if (true === $uri instanceof UriInterface) {
            return $uri;
        }

        return new Uri($uri);
    }

    /**
     * Make a relative URI absolute.
     *
     * @param UriInterface|string $uri
     * @param UriInterface $base
     * @return UriInterface
     */
    public static function absolutify($uri, UriInterface $base): UriInterface
    {
        if ('' === $uri || '//' === $uri) {
            return $base;
        }

        if (false === $uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }

        return UriResolver::resolve($base, $uri);
    }

    /**
     * Generate normalised hash to uniquely represent a given URI.
     *
     * @param UriInterface|string $uri
     * @param bool $ignoreScheme
     * @param bool $withQuery
     * @param bool $withFragment
     * @return string
     */
    public static function hash($uri, bool $ignoreScheme = false, bool $withQuery = true, bool $withFragment = true): string
    {
        if (false === $uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }

        if (false === $withQuery) {
            $uri = $uri->withQuery('');
        }

        if (false === $withFragment) {
            $uri = $uri->withFragment('');
        }

        if ('' === $uri->getPath()) {
            $uri = $uri->withPath('/');
        }

        if ('' !== $query = $uri->getQuery()) {
            $params = [];
            parse_str($query, $params);
            $query = http_build_query(static::sortQuery($params));
            $uri = $uri->withQuery($query);
        }

        $final = $ignoreScheme && $uri->getScheme() ? substr($uri, strpos($uri, '://') + 3) : (string) $uri;

        return md5($final);
    }

    /**
     * Sort query string parameters recursively by key.
     *
     * @param array $params
     * @return array
     */
    protected static function sortQuery(array $params): array
    {
        ksort($params);

        foreach ($params as $key => $value) {
            if (true === is_array($value)) {
                $params[$key] = static::sortQuery($value);
            }
        }

        return $params;
    }

    /**
     * Create a robots.txt for a given URI host's.
     *
     * @param UriInterface|string $uri
     * @return UriInterface
     */
    public static function robotify($uri): UriInterface
    {
        return static::create($uri)->withPath('/robots.txt')->withQuery('')->withFragment('');
    }
}
