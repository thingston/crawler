<?php

require __DIR__ . '/../vendor/autoload.php';

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Thingston\Crawler\Crawler;

$logger = new Logger('crawler-' . uniqid());
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));

$crawler = new Crawler('MyBot/1.0', $logger);
$crawler->start('https://www.w3.org/');
