<?php

require __DIR__ . '/../vendor/autoload.php';

use Thingston\Crawler\Crawler;
use Thingston\Crawler\Observer\LinksObserver;
use Thingston\Crawler\Observer\RedirectionObserver;

$crawler = new Crawler('MyBot/1.0', [
    new RedirectionObserver(),
    new LinksObserver(),
]);

$crawler->setDepth(1)
        ->setLimit(20);

$crawler->start('https://www.w3.org/');

foreach ($crawler->getCrawledCollection() as $crawled) {
    $datetime = $crawled->getCrawled()->format('c');
    $status = $crawled->getStatus();
    $uri = $crawled->getUri();
    $duration = number_format($crawled->getDuration(), 2);
    $length = $crawled->getLength();

    echo sprintf('%s [%s] %s (%s secs, %s bytes)', $datetime, $status, $uri, $duration, $length) . PHP_EOL;
}