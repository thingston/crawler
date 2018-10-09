<?php

/**
 * Thingston Crawler
 *
 * @version 0.1.1
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler;

use DateTime;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Thingston\Crawler\Crawlable\CrawlableCollectionInterface;
use Thingston\Crawler\Crawlable\CrawlableQueueInterface;
use Thingston\Crawler\Observer\ObserverInterface;
use Thingston\Crawler\Profiler\ProfilerInterface;
use Thingston\Crawler\Profiler\SameHostProfiler;

/**
 * Crawler.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class Crawler
{

    /**
     * Default timeout (seconds).
     */
    const DEFAULT_TIMEOUT = 10;

    /**
     * Default number of concurrent connections.
     */
    const DEFAULT_CONCURRENCY = 5;

    /**
     * Default limit number of requests.
     */
    const DEFAULT_LIMIT = 0;

    /**
     * Default max depth.
     */
    const DEFAULT_DEPTH = 0;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var CrawlableQueueInterface
     */
    private $crawlingQueue;

    /**
     * @var CrawlableCollectionInterface
     */
    private $crawledCollection;

    /**
     * @var ProfilerInterface
     */
    private $profiler;

    /**
     * @var string|null
     */
    private $userAgent;

    /**
     * @var int
     */
    private $timeout = self::DEFAULT_TIMEOUT;

    /**
     * @var int
     */
    private $concurrency = self::DEFAULT_CONCURRENCY;

    /**
     * @var int
     */
    private $limit = self::DEFAULT_LIMIT;

    /**
     * @var int
     */
    private $depth = self::DEFAULT_DEPTH;

    /**
     * @var array
     */
    private $observers = [];

    /**
     * Set client.
     *
     * @param ClientInterface $client
     * @return Crawler
     */
    public function setClient(ClientInterface $client): Crawler
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Getrequest headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];

        if (true === isset($this->userAgent)) {
            $headers['User-Agent'] = $this->userAgent;
        }

        return $headers;
    }

    /**
     * Get client.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        if (null === $this->client) {
            $this->client = new Client([
                RequestOptions::HEADERS => $this->getHeaders(),
                RequestOptions::ALLOW_REDIRECTS => false,
                RequestOptions::COOKIES => true,
                RequestOptions::CONNECT_TIMEOUT => $this->timeout,
                RequestOptions::TIMEOUT => $this->timeout,
            ]);
        }

        return $this->client;
    }

    /**
     * Set crawling queue.
     *
     * @param CrawlableQueueInterface $crawlingQueue
     * @return Crawler
     */
    public function setCrawlingQueue(CrawlableQueueInterface $crawlingQueue): Crawler
    {
        $this->crawlingQueue = $crawlingQueue;

        return $this;
    }

    /**
     * Get crawling queue.
     *
     * @return CrawlableQueueInterface
     */
    public function getCrawlingQueue(): CrawlableQueueInterface
    {
        if (null === $this->crawlingQueue) {
            $this->crawlingQueue = new Crawlable\CrawlableQueue();
        }

        return $this->crawlingQueue;
    }

    /**
     * Set crawled collection.
     *
     * @param CrawlableCollectionInterface $crawledCollection
     * @return Crawler
     */
    public function setCrawledCollection(CrawlableCollectionInterface $crawledCollection): Crawler
    {
        $this->crawledCollection = $crawledCollection;

        return $this;
    }

    /**
     * Get crawled collection.
     *
     * @return CrawlableCollectionInterface
     */
    public function getCrawledCollection(): CrawlableCollectionInterface
    {
        if (null === $this->crawledCollection) {
            $this->crawledCollection = new Crawlable\CrawlableCollection();
        }

        return $this->crawledCollection;
    }

    /**
     * Set profiler.
     *
     * @param ProfilerInterface $profiler
     * @return Crawler
     */
    public function setProfiler(ProfilerInterface $profiler): Crawler
    {
        $this->profiler = $profiler;

        return $this;
    }

    /**
     * Get profiler.
     *
     * @return ProfilerInterface
     */
    public function getProfiler(): ProfilerInterface
    {
        if (null === $this->profiler) {
            $this->profiler = new SameHostProfiler();
        }

        return $this->profiler;
    }

    /**
     * Set user agent.
     *
     * @param string $userAgent
     * @return Crawler
     */
    public function setUserAgent(string $userAgent): Crawler
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get user agent.
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Set timeout limit.
     *
     * @param int $timeout
     * @return Crawler
     */
    public function setTimeout(int $timeout): Crawler
    {
        $this->timeout = abs($timeout);

        return $this;
    }

    /**
     * Get timeout.
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set concurrency limit.
     *
     * @param int $concurrency
     * @return Crawler
     */
    public function setConcurrency(int $concurrency): Crawler
    {
        $this->concurrency = abs($concurrency);

        return $this;
    }

    /**
     * Get concurrency.
     *
     * @return int
     */
    public function getConcurrency(): int
    {
        return $this->concurrency;
    }

    /**
     * Set limit limit.
     *
     * @param int $limit
     * @return Crawler
     */
    public function setLimit(int $limit): Crawler
    {
        $this->limit = abs($limit);

        return $this;
    }

    /**
     * Get limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set depth depth.
     *
     * @param int $depth
     * @return Crawler
     */
    public function setDepth(int $depth): Crawler
    {
        $this->depth = abs($depth);

        return $this;
    }

    /**
     * Get depth.
     *
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * Add observer.
     *
     * @param ObserverInterface $observer
     * @return Crawler
     */
    public function addObserver(ObserverInterface $observer): Crawler
    {
        if (false === $this->hasObserver($observer)) {
            array_push($this->observers, $observer);
        }

        return $this;
    }

    /**
     * Check either a given observer is already registered.
     *
     * @param ObserverInterface $observer
     * @return bool
     */
    public function hasObserver(ObserverInterface $observer): bool
    {
        foreach ($this->observers as $obs) {
            if ($observer == $obs) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove a registered observer.
     *
     * @param ObserverInterface $observer
     * @return Crawler
     */
    public function removeObserver(ObserverInterface $observer): Crawler
    {
        foreach ($this->observers as $key => $obs) {
            if ($observer == $obs) {
                unset($this->observers[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Get observers.
     *
     * @return array
     */
    public function getObservers(): array
    {
        return $this->observers;
    }

    /**
     * Get pool request from crawling queue.
     *
     * @return Generator
     */
    protected function getPoolRequests(): Generator
    {
        $crawling = $this->getCrawlingQueue();
        $crawled = $this->getCrawledCollection();

        /* @var $crawlable CrawlableInterface */
        while ($crawlable = $crawling->dequeue()) {
            if (0 < $this->limit && $this->limit < $crawled->count()) {
                $crawling->clear();
                continue;
            }

            if ($this->depth < $crawlable->getDepth()) {
                continue;
            }

            if (false === $this->getProfiler()->crawl($crawlable)) {
                continue;
            }

            $crawled->add($crawlable);
            $requeest = new Request('GET', $crawlable->getUri());

            /* @var $observer ObserverInterface */
            foreach ($this->getObservers() as $observer) {
                $observer->request($requeest, $crawlable, $this);
            }

            yield $crawlable->getId() => $requeest;
        }
    }

    /**
     * Start crawler.
     *
     * @param UriInterface|string|null $uri
     */
    public function start($uri = null)
    {
        $crawling = $this->getCrawlingQueue();

        if (null !== $uri) {
            $crawlable = new Crawlable(UriFactory::create($uri));
            $crawling->enqueue($crawlable);
        }

        $client = $this->getClient();

        while (false === $crawling->isEmpty()) {
            $pool = new Pool($client, $this->getPoolRequests(), [
                'concurrency' => $this->concurrency,
                'options' => $client->getConfig(),
                'fulfilled' => [$this, 'fulfilled'],
                'rejected' => [$this, 'rejected'],
            ]);

            $promise = $pool->promise();
            $promise->wait();
        }
    }

    /**
     * Crawl a single resource.
     *
     * @param CrawlableInterface $crawlable
     * @return ResponseInterface
     */
    public function crawl(Crawlable\CrawlableInterface $crawlable): ResponseInterface
    {
        try {
            $response = $this->getClient()->request('GET', $crawlable->getUri());
            $status = $response->getStatusCode();
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();
            $response = $e->getResponse();
        } catch (RequestException $e) {
            if (false === $e->hasResponse()) {
                throw $e;
            }

            $status = $e->getResponse()->getStatusCode();
            $response = $e->getResponse();
        }

        $crawled = new DateTime();

        $crawlable->setCrawled($crawled)->setStatus($status);
        $this->getCrawledCollection()->add($crawlable);

        return $response;
    }

    /**
     * Callable method for fulfilled requests.
     *
     * @param ResponseInterface $response
     * @param string $key
     */
    public function fulfilled(ResponseInterface $response, string $key)
    {
        $crawlable = $this->getCrawledCollection()->get($key);
        //dump($response->getStatusCode() . ' -> ' . $crawlable->getUri());

        /* @var $observer ObserverInterface */
        foreach ($this->getObservers() as $observer) {
            $observer->fulfilled($response, $crawlable, $this);
        }
    }

    /**
     * Callable method for rejected requests.
     *
     * @param TransferException $reason
     * @param string $key
     */
    public function rejected(TransferException $reason, string $key)
    {
        $crawlable = $this->getCrawledCollection()->get($key);

        /* @var $observer ObserverInterface */
        foreach ($this->getObservers() as $observer) {
            $observer->rejected($reason, $crawlable, $this);
        }
    }
}
