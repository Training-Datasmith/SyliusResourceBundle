<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Resource\Metadata\Util;

use Psr\Cache\Cache_Exception;
use Psr\Cache\Cache_Item_Pool_Interface;
/**
 * This trait in inspired by this API Platform one:
 *
 * @see https://github.com/api-platform/core/blob/main/src/Metadata/Util/CachedTrait.php
 *
 * @internal
 */
trait Cached_Trait
{
    private Cache_Item_Pool_Interface $cache_item_pool;
    /** @var array<string, mixed> */
    private array $local_cache = [];
    private function get_cached(string $cache_key, callable $get_value): mixed
    {
        if (\array_key_exists($cache_key, $this->local_cache)) {
            return $this->local_cache[$cache_key];
        }
        try {
            $cache_item = $this->cache_item_pool->get_item($cache_key);
        } catch (Cache_Exception) {
            return $this->local_cache[$cache_key] = $get_value();
        }
        if ($cache_item->is_hit()) {
            return $this->local_cache[$cache_key] = $cache_item->get();
        }
        $value = $get_value();
        $cache_item->set($value);
        $this->cache_item_pool->save($cache_item);
        return $this->local_cache[$cache_key] = $value;
    }
}