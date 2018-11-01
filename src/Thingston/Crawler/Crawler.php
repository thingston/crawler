<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler;

use Exception;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RobotsTxtParser;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawlable\CrawlableCollection;
use Thingston\Crawler\Crawlable\CrawlableCollectionInterface;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawlable\CrawlableQueue;
use Thingston\Crawler\Crawlable\CrawlableQueueInterface;
use Thingston\Crawler\Observer\ObserverInterface;
use Thingston\Crawler\Profiler\ProfilerInterface;
use Thingston\Crawler\Profiler\SameHostProfiler;

/**
 * Crawler.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class Crawler implements LoggerAwareInterface
{

    /**
     * Implements LoggerAwareInterface
     */
    use LoggerAwareTrait;

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
     * Default flag to respect robots.txt
     */
    const DEFAULT_RESPECT_ROBOTS = true;

    /**
     * Default flag to respect periodicity
     */
    const DEFAULT_RESPECT_PERIODICITY = true;

    /**
     * Priority values
     */
    const PRIORITY_HIGHEST = 50;
    const PRIORITY_HIGH = 25;
    const PRIORITY_NORMAL = 0;
    const PRIORITY_LOW = -25;
    const PRIORITY_LOWEST = -50;

    /**
     * Logger messages.
     */
    const LOG_START = 'Crawler started.';
    const LOG_COMPLETED = 'Crawler completed.';
    const LOG_CLIENT_SET = 'New client set.';
    const LOG_CLIENT_CREATED = 'New client created.';
    const LOG_CRAWLING_QUEUE_SET = 'New crawling queue set.';
    const LOG_CRAWLING_QUEUE_CREATED = 'New crawling queue created.';
    const LOG_CRAWLED_COLLECTION_SET = 'New crawled collection set.';
    const LOG_CRAWLED_COLLECTION_CREATED = 'New crawled collection created.';
    const LOG_PROFILER_SET = 'New profiler set.';
    const LOG_PROFILER_CREATED = 'New profiler created.';
    const LOG_USER_AGENT_SET = 'User-Agent set.';
    const LOG_TIMEOUT_SET = 'Timeout set.';
    const LOG_CONCURRENCY_SET = 'Concurrency set.';
    const LOG_LIMIT_SET = 'Limit set.';
    const LOG_DEPTH_SET = 'Max depth set.';
    const LOG_RESPECT_ROBOTS_SET = 'Respect robots set.';
    const LOG_RESPECT_PERIODICITY_SET = 'Respect periodicity set.';
    const LOG_OBSERVER_ADDED = 'Observer added.';
    const LOG_OBSERVER_REMOVED = 'Observer removed.';
    const LOG_POOL_CREATED = 'New pool created.';
    const LOG_URI_ADDED = 'URI added to collection.';
    const LOG_REQUEST_SENT = 'Request sent.';
    const LOG_DURATION = 'Request duration.';
    const LOG_RESPONSE = 'Response received.';
    const LOG_NO_RESPONSE = 'No response received.';
    const LOG_HEADERS = 'HTTP headers received.';
    const LOG_LIMIT_REACH = 'Limit reach';
    const LOG_TOO_DEEP = 'Too deep.';
    const LOG_PROFILE_REFUSED = 'Profile refused.';
    const LOG_ROBOTS_DISALLOWED = 'Disalloed by robots.txt.';

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
    private $timeout;

    /**
     * @var int
     */
    private $concurrency;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var bool
     */
    private $respectRobots;

    /**
     * @var bool
     */
    private $respectPeriodicity;

    /**
     * @var array
     */
    private $observers = [];

    /**
     * @var array
     */
    private $summary = [];

    /**
     * Create new instance.
     *
     * @param string $userAgent
     * @param LoggerInterface $logger
     */
    public function __construct(string $userAgent, array $observers = [], LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();

        $this->setUserAgent($userAgent)
                ->setTimeout(self::DEFAULT_TIMEOUT)
                ->setConcurrency(self::DEFAULT_CONCURRENCY)
                ->setLimit(self::DEFAULT_LIMIT)
                ->setDepth(self::DEFAULT_DEPTH)
                ->setRespectRobots(self::DEFAULT_RESPECT_ROBOTS)
                ->setRespectPeriodicity(self::DEFAULT_RESPECT_PERIODICITY);

        foreach ($observers as $observer) {
            $this->addObserver($observer);
        }
    }

    /**
     * Set client.
     *
     * @param ClientInterface $client
     * @return Crawler
     */
    public function setClient(ClientInterface $client): Crawler
    {
        $this->client = $client;
        $this->logger->debug(self::LOG_CLIENT_SET);

        return $this;
    }

    /**
     * Get request headers.
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

            $this->logger->debug(self::LOG_CLIENT_CREATED);
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
        $this->logger->debug(self::LOG_CRAWLING_QUEUE_SET);

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
            $this->crawlingQueue = new CrawlableQueue();
            $this->logger->debug(self::LOG_CRAWLING_QUEUE_CREATED);
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
        $this->logger->debug(self::LOG_CRAWLED_COLLECTION_SET);

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
            $this->crawledCollection = new CrawlableCollection();
            $this->logger->debug(self::LOG_CRAWLED_COLLECTION_CREATED);
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
        $this->logger->debug(self::LOG_PROFILER_SET);

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
            $this->logger->debug(self::LOG_PROFILER_CREATED);
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
        $this->logger->debug(self::LOG_USER_AGENT_SET, ['user_agent' => $userAgent]);

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
        $this->logger->debug(self::LOG_TIMEOUT_SET, ['timeout' => $timeout]);

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
        $this->logger->debug(self::LOG_CONCURRENCY_SET, ['concurrency' => $concurrency]);

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
        $this->logger->debug(self::LOG_LIMIT_SET, ['limit' => $limit]);

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
        $this->logger->debug(self::LOG_DEPTH_SET, ['depth' => $depth]);

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
     * Set robots flag.
     *
     * @param bool $respectRobots
     * @return Crawler
     */
    public function setRespectRobots(bool $respectRobots): Crawler
    {
        $this->respectRobots = $respectRobots;
        $this->logger->debug(self::LOG_RESPECT_ROBOTS_SET, ['respect_robots' => $respectRobots]);

        return $this;
    }

    /**
     * Get robots flag.
     *
     * @return bool
     */
    public function getRespectRobots(): bool
    {
        return $this->respectRobots;
    }

    /**
     * Set periodicity flag.
     *
     * @param bool $respectPeriodicity
     * @return Crawler
     */
    public function setRespectPeriodicity(bool $respectPeriodicity): Crawler
    {
        $this->respectPeriodicity = $respectPeriodicity;
        $this->logger->debug(self::LOG_RESPECT_PERIODICITY_SET, ['respect_periodicity' => $respectPeriodicity]);

        return $this;
    }

    /**
     * Get periodicity flag.
     *
     * @return bool
     */
    public function getRespectPeriodicity(): bool
    {
        return $this->respectPeriodicity;
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
            $this->logger->debug(self::LOG_OBSERVER_ADDED, ['observer' => get_class($observer)]);
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
                $this->logger->debug(self::LOG_OBSERVER_REMOVED, ['observer' => get_class($observer)]);
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
     * Get logger.
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get robots parser that applies for a given URI.
     *
     * @param UriInterface $uri
     * @param bool $retry
     * @return RobotsTxtParser|null
     */
    public function getRobots(UriInterface $uri, bool $retry = true): ?RobotsTxtParser
    {
        $crawlable = new Crawlable($uri->withPath('/robots.txt')->withQuery('')->withFragment(''));
        $key = $crawlable->getKey();

        $crawleds = $this->getCrawledCollection();

        if (false === $crawleds->has($key)) {
            if (false === $retry) {
                return null;
            }

            $this->crawl($crawlable);

            return $this->getRobots($uri, false);
        }

        $body = $crawleds->get($key)->getBody() ?? '';

        return new RobotsTxtParser($body);
    }

    /**
     * Get pool request from crawling queue.
     *
     * @return Generator
     */
    protected function getPoolRequests(): Generator
    {
        $crawling = $this->getCrawlingQueue();
        $crawleds = $this->getCrawledCollection();

        /* @var $crawlable CrawlableInterface */
        while ($crawlable = $crawling->dequeue()) {
            if (0 < $this->limit && $this->limit <= $crawleds->count()) {
                $crawling->clear();
                $this->logger->debug(self::LOG_LIMIT_REACH);
                continue;
            }

            if ($this->depth < $depth = $crawlable->getDepth()) {
                $this->logger->debug(self::LOG_TOO_DEEP, ['uri' => (string) $crawlable->getUri(), 'depth' => $depth]);
                continue;
            }

            if (false === $this->getProfiler()->crawl($crawlable)) {
                $this->logger->debug(self::LOG_PROFILE_REFUSED, ['uri' => (string) $crawlable->getUri()]);
                continue;
            }

            if (null !== $crawled = $crawleds->get($crawlable->getKey())) {
                if ($crawled->getCrawled()->getTimestamp() > time() - 60) {
                    continue;
                }

                if (true === $this->respectPeriodicity && false === $crawled->isPeriodicity()) {
                    continue;
                }

                if (null !== $crawlable->getModified() && $crawlable->getModified()->getTimestamp() < $crawled->getCrawled()->getTimestamp()) {
                    continue;
                }
            }

            if (true === $this->respectRobots && '/robots.txt' !== $crawlable->getUri()->getPath()) {
                $uri = $crawlable->getUri();
                $robots = $this->getRobots($uri);

                if (null !== $robots && true === $robots->isDisallowed($uri, $this->userAgent)) {
                    $this->logger->debug(self::LOG_ROBOTS_DISALLOWED, ['uri' => (string) $crawlable->getUri()]);
                    continue;
                }
            }

            $requeest = new Request('GET', $crawlable->getUri());

            /* @var $observer ObserverInterface */
            foreach ($this->getObservers() as $observer) {
                $observer->request($requeest, $crawlable, $this);
            }

            $crawlable->setStart(microtime(true));
            $crawleds->add($crawlable);

            $this->logger->debug(self::LOG_URI_ADDED, ['uri' => (string) $crawlable->getUri()]);
            $this->logger->info(self::LOG_REQUEST_SENT, ['uri' => (string) $crawlable->getUri()]);

            yield $crawlable->getKey() => $requeest;
        }
    }

    /**
     * Start crawler.
     *
     * @param UriInterface|string|null $uri
     */
    public function start($uri = null)
    {
        $this->logger->info(self::LOG_START);

        $start = microtime(true);

        $this->summary['start'] = date('c', round($start));
        $this->summary['duration'] = 0;
        $this->summary['count'] = 0;

        $crawling = $this->getCrawlingQueue();

        if (null !== $uri) {
            $crawlable = new Crawlable(UriFactory::create($uri));
            $crawling->enqueue($crawlable);
        }

        $client = $this->getClient();
        $options = $client->getConfig();
        //dump($options);exit;

        while (false === $crawling->isEmpty()) {
            $pool = new Pool($client, $this->getPoolRequests(), [
                'concurrency' => $this->concurrency,
                'options' => $options,
                'fulfilled' => [$this, 'fulfilled'],
                'rejected' => [$this, 'rejected'],
            ]);

            $this->logger->debug(self::LOG_POOL_CREATED, [
                'concurrency' => $this->concurrency,
                'headers' => $options['headers'],
            ]);

            $promise = $pool->promise();
            $promise->wait();
        }

        $this->summary['duration'] = microtime(true) - $start;

        $this->logger->info(self::LOG_COMPLETED, $this->summary);
    }

    /**
     * Crawl a single resource.
     *
     * @param CrawlableInterface $crawlable
     * @return void
     */
    public function crawl(CrawlableInterface $crawlable)
    {
        $crawlable->setStart(microtime(true));
        $this->getCrawledCollection()->add($crawlable);

        $this->logger->debug(self::LOG_URI_ADDED, ['uri' => (string) $crawlable->getUri()]);

        $key = $crawlable->getKey();

        $this->logger->info(self::LOG_REQUEST_SENT, ['uri' => (string) $crawlable->getUri()]);

        try {
            $response = $this->getClient()->request('GET', $crawlable->getUri());
            $this->fulfilled($response, $key);
        } catch (Exception $reason) {
            $this->rejected($reason, $key);
        }
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
        $this->updateCrawlable($crawlable, $response);

        /* @var $observer ObserverInterface */
        foreach ($this->getObservers() as $observer) {
            $observer->fulfilled($response, $crawlable, $this);
        }
    }

    /**
     * Callable method for rejected requests.
     *
     * @param Exception $reason
     * @param string $key
     */
    public function rejected(Exception $reason, string $key)
    {
        $crawlable = $this->getCrawledCollection()->get($key);

        if ($reason instanceof RequestException && $reason->hasResponse()) {
            $response = $reason->getResponse();
        } else {
            $response = new Response(500, [], $reason->getMessage());
            $this->logger->notice(self::LOG_NO_RESPONSE, ['uri' => (string) $crawlable->getUri()]);
        }

        $this->updateCrawlable($crawlable, $response);

        /* @var $observer ObserverInterface */
        foreach ($this->getObservers() as $observer) {
            $observer->rejected($reason, $crawlable, $this);
        }
    }

    /**
     * Update crawlable after a request.
     *
     * @param CrawlableInterface $crawlable
     * @param ResponseInterface $response
     * @return void
     */
    protected function updateCrawlable(CrawlableInterface $crawlable, ResponseInterface $response)
    {
        if (null !== $start = $crawlable->getStart()) {
            $crawlable->setDuration(microtime(true) - $start);

            $this->logger->info(self::LOG_DURATION, [
                'uri' => (string) $crawlable->getUri(),
                'duration' => $crawlable->getDuration(),
            ]);
        }

        $body = $response->getBody()->getContents() ?? '';
        $response->getBody()->rewind();

        $crawlable->setStatus($response->getStatusCode() ?? 500)
                ->setHeaders($response->getHeaders() ?? [])
                ->setBody($body);

        if (true === $response->hasHeader('Content-Type')) {
            $header = current($response->getHeader('Content-Type'));
            $mimeType = explode(';', $header)[0];
            $crawlable->setMimeType($mimeType);
        }

        $this->getCrawledCollection()->set($crawlable->getKey(), $crawlable);

        $status = $crawlable->getStatus();

        $this->summary['count']++;
        $this->summary[$status] = true === isset($this->summary[$status]) ? $this->summary[$status] + 1 : 1;

        $this->logger->info(self::LOG_RESPONSE, [
            'uri' => (string) $crawlable->getUri(),
            'status' => $crawlable->getStatus(),
            'length' => $crawlable->getLength(),
            'type' => $crawlable->getMimeType(),
        ]);
    }
}
