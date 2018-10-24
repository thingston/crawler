<?php

require __DIR__ . '/../vendor/autoload.php';

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\Observer\RedirectionObserver;
use Thingston\Crawler\Observer\RobotsObserver;
use Thingston\Crawler\Observer\SitemapObserver;

$logger = new Logger('crawler-' . uniqid());
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));

$crawler = new Crawler('MyBot/1.0', [
    new RedirectionObserver(),
    new RobotsObserver(),
    new SitemapObserver(),
], $logger);

$crawler->setDepth(2);
$crawler->start('https://www.sitemaps.org/');

