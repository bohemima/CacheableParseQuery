<?php

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PhpFileCache;
use Funkardotnu\Parse\CacheableParseQuery;
use Parse\ParseClient;
use Parse\ParseObject;

class CacheableParseQueryTest extends PHPUnit_Framework_TestCase {
	private $testParseClassName = 'TestObject';

	public function setUp() {
		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
		date_default_timezone_set('UTC');
		ParseClient::initialize(
			'app-id-here',
			'rest-api-key-here',
			'master-key-here'
		);

		$this->_clearClass('TestObject');
	}

	private function _clearClass($className) {
		$query = new CacheableParseQuery($className);
		$query->each(function(ParseObject $obj) {
			$obj->destroy(true);
		}, true);
	}

	public function tearDown() {
		// void
	}

	private function _addTestObjects($numberOfObjects = 5) {
		$objects = [];
		for ($i = 0; $i < $numberOfObjects; $i++) {
			$object = new ParseObject($this->testParseClassName);
			$object->set('String', 'String' . $i);

			$objects[] = $object;
		}

		ParseObject::saveAll($objects);
	}

	public function testClass() {
		$query = new CacheableParseQuery($this->testParseClassName);
		$this->assertInstanceOf('Funkardotnu\Parse\CacheableParseQuery', $query);
	}

	public function testProperties() {
		$query = new CacheableParseQuery($this->testParseClassName);
		$cache = new PhpFileCache('./cache');
		$cacheEnabled = false;
		$cacheLifetime = 300;

		$query->setCacheProvider($cache);
		$query->setCacheEnabled($cacheEnabled);
		$query->setCacheLifetime($cacheLifetime);

		$this->assertEquals($cache, $query->getCacheProvider());
		$this->assertEquals($cacheEnabled, $query->isCacheEnabled());
		$this->assertEquals($cacheLifetime, $query->getCacheLifetime());

		// Set some new values
		$newCache = new PhpFileCache('./cache');
		$newCacheEnabled = true;
		$newCacheLifetime = 600;

		$query->setCacheProvider($newCache);
		$query->setCacheEnabled($newCacheEnabled);
		$query->setCacheLifetime($newCacheLifetime);

		$this->assertEquals($newCache, $query->getCacheProvider());
		$this->assertEquals($newCacheEnabled, $query->isCacheEnabled());
		$this->assertEquals($newCacheLifetime, $query->getCacheLifetime());
	}

	public function testQueryCache() {
		$this->_addTestObjects(5);

		$query = new CacheableParseQuery($this->testParseClassName);
		$cache = new PhpFileCache('./cache');
		$query->setCacheProvider($cache);
		$query->clearAllCachedResults();

		$query->limit(3);

		// First time querying, result should not be cached
		$this->assertFalse($query->hasCachedResult());
		$results = $query->find();

		// We should get three objects back
		$this->assertEquals(3, sizeof($results));

		// Query should now be cached
		$this->assertTrue($query->hasCachedResult());

		// Change limit
		$query->limit(2);

		// Query should now not be cached, since a parameter has changed
		$this->assertFalse($query->hasCachedResult());

		// Change limit back to 3, query should now have a cached result again
		$query->limit(3);
		$this->assertTrue($query->hasCachedResult());

		// Do a query, make sure we still get 3 objects back
		$results2 = $query->find();
		$this->assertEquals(3, sizeof($results2));

		$object = $results2[0];
		$this->assertInstanceOf('Parse\ParseObject', $object);

		// Clear class and query again, we should still get back 3 results - as they are cached
		$this->_clearClass($this->testParseClassName);
		$results2 = $query->find();
		$this->assertEquals(3, sizeof($results2));

		// Change cache provider, result should not be cached anymore..
		$cache2 = new FilesystemCache('./cache');
		$query->setCacheProvider($cache2);

		$this->assertFalse($query->hasCachedResult());
	}

	public function testQueryCacheLifetime() {
		$this->_addTestObjects(5);

		$query = new CacheableParseQuery($this->testParseClassName);
		$cache = new PhpFileCache('./cache');
		$query->setCacheProvider($cache);
		$query->setCacheLifetime(3);
		$query->clearAllCachedResults();

		$query->limit(5);
		$results = $query->find();
		$this->assertEquals(5, sizeof($results));

		$this->assertTrue($query->hasCachedResult());

		// Sleep 4 seconds, cache should expire after 3..
		sleep(4);

		$this->assertFalse($query->hasCachedResult());
	}
} 