<?php

namespace Funkardotnu\Parse;

use \Parse\ParseQuery;
use \Doctrine\Common\Cache\CacheProvider;

/**
 * CacheableParseQuery - Handles querying data from Parse - with cache!
 *
 * @package Funkardotnu\Parse
 * @author Henrik Malmberg <henrik@funkar.nu>
 */
class CacheableParseQuery extends ParseQuery {
	/**
	 * @var string Class Name for data stored on Parse.
	 */
	private $className;

    /**
     * @var int The cache lifetime
     */
    private $cacheLifetime = 3600;

    /**
     * @var bool Defines if we want to use cache or not
     */
    private $cacheEnabled = true;

    /**
     * @var CacheProvider Cache provider to use
     */
    private $cacheProvider;

	/**
	 * @inheritdoc
	 */
	public function __construct($className) {
		// We need to do this, because className is private in ParseQuery class.
		// We'll use the class name as a part of the cacheKey
		$this->className = $className;
		return parent::__construct($className);
	}

    /**
     * @inheritdoc
     * @throws \BadFunctionCallException If not CacheProvider has been set.
     */
    public function find($useMasterKey = false) {
        if ($this->cacheEnabled && !$this->cacheProvider instanceof CacheProvider) {
            throw new \BadFunctionCallException('Cache is enabled but no CacheProvider has been set.');
        }

        // Try cache first
        if ($this->cacheEnabled) {
            $cacheKey = $this->getCacheKey();
            if ($this->cacheProvider->contains($cacheKey)) {
                $cachedResult = unserialize($this->cacheProvider->fetch($cacheKey));
                if ($cachedResult !== false) {
                    return $cachedResult;
                }
            }
        }

        // Item was not found in cache
        $result = parent::find($useMasterKey);

        if ($this->cacheEnabled) {
            $this->cacheProvider->save($cacheKey, serialize($result), $this->cacheLifetime);
        }

        return $result;
    }

	/**
	 * Test if an entry exists in the cache.
	 *
	 * @return bool TRUE if the current ClassName & query parameters exists in the cache.
	 * @throws \BadFunctionCallException If not CacheProvider has been set.
	 */
	public function hasCachedResult() {
		if (!$this->cacheProvider instanceof CacheProvider) {
			throw new \BadFunctionCallException('No CacheProvider has been set.');
		}

		$cacheKey = $this->getCacheKey();
		return $this->cacheProvider->contains($cacheKey);
	}

	/**
	 * Deletes the current cache entry for this query.
	 *
	 * @return bool TRUE if the cache entry was successfully deleted, FALSE otherwise.
	 * @throws \BadFunctionCallException
	 */
	public function clearCachedResult() {
		if (!$this->cacheProvider instanceof CacheProvider) {
			throw new \BadFunctionCallException('No CacheProvider has been set.');
		}

		$cacheKey = $this->getCacheKey();
		return $this->cacheProvider->delete($cacheKey);
	}

	/**
	 * Returns a md5 representation of the current query.
	 *
	 * @return string A md5 string respresentation of the current query.
	 */
	private function getCacheKey() {
		return md5($this->className . serialize($this->_getOptions()));
	}

    /**
     * Clears all cached query entries.
     *
     * @return bool TRUE if all the cache entries were successfully deleted, FALSE otherwise.
     * @throws \BadFunctionCallException If not CacheProvider has been set.
     */
    public function clearAllCachedResults() {
        if (!$this->cacheProvider instanceof CacheProvider) {
            throw new \BadFunctionCallException('No CacheProvider has been set.');
        }

        return $this->cacheProvider->deleteAll();
    }

    /**
     * @return boolean Indicates if we cache is enabled.
     */
    public function isCacheEnabled() {
        return $this->cacheEnabled;
    }

    /**
     * @param boolean $useCache Set if we should enable cache.
     */
    public function setCacheEnabled($useCache) {
        $this->cacheEnabled = (bool)$useCache;
    }

    /**
     * @return int Get cache lifetime (0 = forever).
     */
    public function getCacheLifetime() {
        return $this->cacheLifetime;
    }

    /**
     * @param int $lifeTime Sets a specific lifetime for cache entries (0 = forever).
     */
    public function setCacheLifetime($lifeTime) {
        $this->cacheLifetime = $lifeTime;
    }

    /**
     * @return CacheProvider Cache provider being used.
     */
    public function getCacheProvider() {
        return $this->cacheProvider;
    }

    /**
     * @param CacheProvider $cacheProvider Sets the cache provider to use.
     */
    public function setCacheProvider($cacheProvider) {
        $this->cacheProvider = $cacheProvider;
    }
} 
