<?php

require __DIR__ . '/../vendor/autoload.php';

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\Observer\LinksObserver;
use Thingston\Crawler\Observer\RedirectionObserver;

$observers = [
    new RedirectionObserver(),
    new LinksObserver(),
];

$logger = new Logger('crawler-' . uniqid());
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));

$crawler = (new Crawler('MyBot/1.0', $observers, $logger))->start('https://www.w3.org/');
