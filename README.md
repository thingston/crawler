Thingston Crawler
=================

Web crawler based on PHP [Guzzle HTTP Client](http://docs.guzzlephp.org/) with concurrency support for faster operation.
Includes support for any content-type download, link profiler and response observers.

Requirements
------------

Thingston Crawler requires:

-   [PHP 7.1](https://secure.php.net/releases/7_1_0.php) or above.

Instalation
-----------

Add Thingston Crawler to any PHP project using [Composer](https://getcomposer.org/):

```bash
composer require thingston/crawler
```

Getting Started
---------------

Simply create a new `Crawler` instance and invoke `start` method with any public URI:

```php
use Thingston\Crawler;

$crawler = new Crawler();
$crawler->start('https://www.wikipedia.org/');
```

In order to process results from the crawling process you may add as many many Observers.
An Observer is a concrete class implement `Thingston/Crawler/Observer/ObserverInterface`.

Reporting Issues
----------------

In case you find issues with this code please open a ticket in Github Issues at
[https://github.com/thingston/crawler/issues](https://github.com/thingston/crawler/issues).

Contributors
------------

Open Source is made of contribuition. If you want to contribute to Thingston please
follow these steps:

1.  Fork latest version into your own repository.
2.  Write your changes or additions and commit them.
3.  Follow PSR-2 coding style standard.
4.  Make sure you have unit tests with full coverage to your changes.
5.  Go to Github Pull Requests at [https://github.com/thingston/crawler/pulls](https://github.com/thingston/crawler/pulls)
    and create a new request.

Thank you!

Changes and Versioning
----------------------

All relevant changes on this code are logged in a separated [log](CHANGELOG.md) file.

Version numbers follow recommendations from [Semantic Versioning](http://semver.org/).

License
-------

Thingston code is maintained under [The MIT License](https://opensource.org/licenses/MIT).