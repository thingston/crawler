<?php

require __DIR__ . '/../vendor/autoload.php';

use Thingston\Crawler\Crawler;

$crawler = new Crawler('MyBot/1.0');

$crawler->setDepth(1)
        ->setLimit(20);

$crawler->start('https://www.w3.org/');

foreach ($crawler->getCrawledCollection() as $crawled) {
    $datetime = $crawled->getCrawled()->format('c');
    $status = $crawled->getStatus();
    $uri = $crawled->getUri();
    $duration = number_format($crawled->getDuration(), 2);
    $length = strlen($crawled->getBody()->getContents());

    echo sprintf('%s [%s] %s (%s secs, %s bytes)', $datetime, $status, $uri, $duration, $length) . PHP_EOL;
}