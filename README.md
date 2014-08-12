CacheableParseQuery
-------------------

This sweet little thing will give you cache to your ParseQueries, using Doctrine Cache.

Installation
------------

The easiest way is to use composer

```json
{
  "require": {
    "funkardotnu/cacheableparsequery" : "1.0.*"
  }
}
```

Usage
-----

```php
use \Parse\ParseClient;
use \Doctrine\Common\Cache\FilesystemCache; // Or any of the other Doctrine Cache providers
use \Funkardotnu\Parse\CacheableParseQuery;

ParseClient::initialize('...', '...', '...');

// Initialize cache
$cache = new FilesystemCache('./cache/');
$cache->setNamespace('parsequerycache'); // Optionally set a cache namespace

// ** NOTE that we are using CacheableParseQuery and not ParseQuery here
$query = new CacheableParseQuery('SomeClass');
$query->setCacheProvider($cache);
$query->setLifetime(300); // Default is 3600 - 1 hour

$query->find(); // First call will use live data
$query->find(); // This call will use cached data
```

Problem? Idea?
--------------

This small library was whipped up pretty fast and is not yet thoroughly tested, let me know if you encounter any
problems or have ideas how to make it better using Github Issues.

Thank you!
