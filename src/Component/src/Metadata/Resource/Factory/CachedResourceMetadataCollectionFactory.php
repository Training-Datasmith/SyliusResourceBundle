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
namespace Sylius\Resource\Metadata\Resource\Factory;

use Psr\Cache\Cache_Exception;
use Psr\Cache\Cache_Item_Pool_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
/**
 * This class in inspired by this API Platform one:
 *
 * @see https://github.com/api-platform/core/blob/main/src/Metadata/Resource/Factory/CachedResourceMetadataCollectionFactory.php
 */
final class Cached_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public const CACHE_KEY_PREFIX = 'sylius_resource_metadata_collection_';
    private array $local_cache = [];
    public function __construct(private readonly Cache_Item_Pool_Interface $cache_item_pool, private readonly Resource_Metadata_Collection_Factory_Interface $decorated)
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $cache_key = self::CACHE_KEY_PREFIX . md5($resource_class);
        if (\array_key_exists($cache_key, $this->local_cache)) {
            return new Resource_Metadata_Collection($this->local_cache[$cache_key]);
        }
        try {
            $cache_item = $this->cache_item_pool->get_item($cache_key);
        } catch (Cache_Exception) {
            $resource_metadata_collection = $this->decorated->create($resource_class);
            $this->local_cache[$cache_key] = (array) $resource_metadata_collection;
            return $resource_metadata_collection;
        }
        if ($cache_item->is_hit()) {
            $this->local_cache[$cache_key] = $cache_item->get();
            return new Resource_Metadata_Collection($this->local_cache[$cache_key]);
        }
        $resource_metadata_collection = $this->decorated->create($resource_class);
        $this->local_cache[$cache_key] = (array) $resource_metadata_collection;
        $cache_item->set($this->local_cache[$cache_key]);
        $this->cache_item_pool->save($cache_item);
        return $resource_metadata_collection;
    }
}