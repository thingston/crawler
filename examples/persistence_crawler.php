<?php

require __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Thingston\Crawler\Crawlable\Crawlable;
use Thingston\Crawler\Crawler;
use Thingston\Crawler\Crawlable\CrawlableCollection;
use Thingston\Crawler\Storage\PersistentStorage;
use Thingston\Crawler\Observer\LinksObserver;
use Thingston\Crawler\Observer\RedirectionObserver;

/**
 * Database connection
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.8/reference/configuration.html#configuration
 */
$params = [
    'dbname' => 'crawler',
    'user' => 'root',
    'password' => 'Passw0rd',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];
$connection = DriverManager::getConnection($params);

/**
 * Filesystem
 */
$path = __DIR__ . '/storage';
$adapter = new Local($path);
$filesystem = new Filesystem($adapter);

/**
 * Crawlable collection
 */
$storage = new PersistentStorage($connection, $filesystem);
$collection = new CrawlableCollection($storage);
$collection->clear();

/**
 * Observers
 */
$observers = [
    new RedirectionObserver(),
    new LinksObserver(),
];

/**
 * Logger
 */
$logger = new Logger('crawler-' . uniqid());
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));

/**
 * Crawler
 */
$crawler = new Crawler('MyBot/1.0', $observers, $logger);
$crawler->setCrawledCollection($collection)->setDepth(1)->start('https://www.bbc.com/news');
