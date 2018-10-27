<?php

/**
 * Thingston Crawler
 *
 * @link https://github.com/thingston/crawler Public Git repository
 * @copyright (c) 2018, Pedro Ferreira <https://thingston.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Thingston\Crawler\Observer;

use ChromeDevtoolsProtocol\Context;
use ChromeDevtoolsProtocol\Instance\Launcher;
use ChromeDevtoolsProtocol\Model\DOM\GetDocumentRequest;
use ChromeDevtoolsProtocol\Model\DOM\GetOuterHTMLRequest;
use ChromeDevtoolsProtocol\Model\Network\EnableRequest;
use ChromeDevtoolsProtocol\Model\Network\LoadingFailedEvent;
use ChromeDevtoolsProtocol\Model\Network\RequestWillBeSentEvent;
use ChromeDevtoolsProtocol\Model\Network\ResponseReceivedEvent;
use ChromeDevtoolsProtocol\Model\Network\SetUserAgentOverrideRequest;
use ChromeDevtoolsProtocol\Model\Page\NavigateRequest;
use Psr\Http\Message\ResponseInterface;
use Thingston\Crawler\Crawlable\CrawlableInterface;
use Thingston\Crawler\Crawler;

/**
 * Javascript observer.
 *
 * @author Pedro Ferreira <pedro@thingston.com>
 */
class JavascriptObserver extends NullObserver
{

    /**
     * Process a fulfilled request.
     *
     * @param ResponseInterface $response
     * @param CrawlableInterface $crawlable
     * @param Crawler $crawler
     */
    public function fulfilled(ResponseInterface $response, CrawlableInterface $crawlable, Crawler $crawler)
    {
        if (false === $this->isHtml($response)) {
            return;
        }

        $logger = $crawler->getLogger();

        $start = microtime(true);

        $uri = $crawlable->getUri();
        $ctx = Context::withTimeout(Context::background(), $crawler->getTimeout());

        $requests = [];
        $responses = [];
        $failures = [];

        $launcher = new Launcher();
        $launcher->setExecutable('chromium-browser');
        $instance = $launcher->launch($ctx);

        $logger->debug('Chromium browser launched.');

        try {
            $tab = $instance->open($ctx);
            $tab->activate($ctx);

            $logger->debug('Chromium tab open.', ['uri' => (string) $uri]);

            $devtools = $tab->devtools();

            /* @var $network \ChromeDevtoolsProtocol\Domain\NetworkDomain */
            $network = $devtools->network();
            $network->enable($ctx, EnableRequest::builder()->build());
            $network->setUserAgentOverride($ctx, SetUserAgentOverrideRequest::builder()->setUserAgent('FNB/1.0-alpha')->build());

            $network->addRequestWillBeSentListener(function (RequestWillBeSentEvent $event) use (&$requests) {
                if (true === in_array($event->type, ['Document', 'Image', 'Media'])) {
                    $requests[$event->requestId] = $event;
                }
            });

            $network->addResponseReceivedListener(function (ResponseReceivedEvent $event) use (&$responses) {
                if (true === in_array($event->type, ['Document', 'Image', 'Media'])) {
                    $responses[$event->requestId] = $event;
                }
            });

            $network->addLoadingFailedListener(function (LoadingFailedEvent $event) use (&$failures) {
                if (true === in_array($event->type, ['Document', 'Image', 'Media'])) {
                    $failures[$event->requestId] = $event;
                }
            });

            try {
                /* @var $page \ChromeDevtoolsProtocol\Domain\PageDomain */
                $page = $devtools->page();

                $page->enable($ctx);
                $page->navigate($ctx, NavigateRequest::builder()->setUrl((string) $uri)->build());
                $page->awaitLoadEventFired($ctx);

                /* @var $dom \ChromeDevtoolsProtocol\Domain\DomDomain */
                $dom = $devtools->dom();
                $dom->enable($ctx);
                $dom->getDocument($ctx, GetDocumentRequest::builder()->build());
                $outerHTML = $dom->getOuterHTML($ctx, GetOuterHTMLRequest::builder()->setNodeId(1)->build());
                $body = $outerHTML->outerHTML;

                /* @var $event RequestWillBeSentEvent */
                $event = array_shift($requests);

                if (true === isset($responses[$event->requestId])) {
                    $status = $responses[$event->requestId]->response->status;
                    $headers = $responses[$event->requestId]->response->headers->getIterator()->getArrayCopy();
                }

                $duration = microtime(true) - $start;

                $crawlable->setStart($start)->setDuration($duration)->setStatus($status)->setHeaders($headers)->setBody($body);

                $logger->debug(Crawler::LOG_RESPONSE, [
                    'uri' => (string) $uri,
                    'status' => $crawlable->getStatus(),
                    'length' => $crawlable->getLength(),
                    'type' => $crawlable->getMimeType(),
                ]);

                // @todo check for failures
            } finally {
                $devtools->close();
                $logger->debug('Chromium tab closed.', ['uri' => (string) $uri]);
            }
        } finally {
            $instance->close();
            $logger->debug('Chromium browse closed.');
        }
    }
}
