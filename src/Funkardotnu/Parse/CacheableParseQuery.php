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
     * @var int The cache lifetime
     */
    private $lifeTime = 3600;

    /**
     * @var bool Defines if we want to use cache or not
     */
    private $useCache = true;

    /**
     * @var CacheProvider Cache provider to use
     */
    private $cacheProvider;

    /**
     * @inheritdoc
     * @throws \BadFunctionCallException If not CacheProvider has been set.
     */
    public function find($useMasterKey = false) {
        if ($this->useCache && !$this->cacheProvider instanceof CacheProvider) {
            throw new \BadFunctionCallException('Cache is enabled but no CacheProvider has been set.');
        }

        // Try cache first
        if ($this->useCache) {
            $cacheKey = md5(serialize($this->_getOptions()));
            if ($this->cacheProvider->contains($cacheKey)) {
                $cachedResult = unserialize($this->cacheProvider->fetch($cacheKey));
                if ($cachedResult !== false) {
                    return $cachedResult;
                }
            }
        }

        // Item was not found in cache
        $result = parent::find($useMasterKey);

        if ($this->useCache) {
            $this->cacheProvider->save($cacheKey, serialize($result), $this->lifeTime);
        }

        return $result;
    }

    /**
     * @return bool TRUE if the cache entries were successfully deleted, FALSE otherwise.
     * @throws \BadFunctionCallException If not CacheProvider has been set.
     */
    public function clearAllCachedResults() {
        if (!$this->cacheProvider instanceof CacheProvider) {
            throw new \BadFunctionCallException('No CacheProvider has been set.');
        }

        return $this->cacheProvider->deleteAll();
    }

    /**
     * @return boolean Indicates if we should use cache.
     */
    public function getUseCache() {
        return $this->useCache;
    }

    /**
     * @param boolean $useCache Set if we should use cache.
     */
    public function setUseCache($useCache) {
        $this->useCache = (bool)$useCache;
    }

    /**
     * @return int Get cache lifetime (0 = forever).
     */
    public function getLifeTime() {
        return $this->lifeTime;
    }

    /**
     * @param int $lifeTime Sets a specific lifetime for cache entries (0 = forever).
     */
    public function setLifeTime($lifeTime) {
        $this->lifeTime = $lifeTime;
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
