CacheableParseQuery
-------------------

This sweet little thing will give you cache to your ParseQueries, using Doctrine Cache.

Installation
------------

The easiest way is to use composer

```json
{
  "require": {
    "funkardotnu/cacheableparsequery" : "dev-master"
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
$query->limit(3); // Limit to three items, just to test.

echo ($query->hasCachedResult() ? 'From cache' : 'Live data') . ':<br>';
var_dump($query->find()); // First call will use live data

echo ($query->hasCachedResult() ? 'From cache' : 'Live data') . ':<br>';
var_dump($query->find()); // This call will use cached data

// Clear all cached data
$query->clearAllCachedResults();
```

Problem? Idea?
--------------

This small library was whipped up pretty fast and is not yet thoroughly tested, let me know if you encounter any
problems or have ideas how to make it better using Github Issues.

Thank you!
