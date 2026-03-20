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

use Psr\Cache\Cache_Item_Pool_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Class_List;
use Sylius\Resource\Metadata\Util\Cached_Trait;
/**
 * Caches resource class list.
 */
final class Cached_Resource_Class_List_Factory implements Resource_Class_List_Factory_Interface
{
    use Cached_Trait;
    public const CACHE_KEY = 'resource_class_list';
    public function __construct(Cache_Item_Pool_Interface $cache_item_pool, private readonly Resource_Class_List_Factory_Interface $decorated)
    {
        $this->cache_item_pool = $cache_item_pool;
    }
    /**
     * @inheritdoc
     */
    public function create(): Resource_Class_List
    {
        return $this->get_cached(self::CACHE_KEY, fn(): Resource_Class_List => $this->decorated->create());
    }
}