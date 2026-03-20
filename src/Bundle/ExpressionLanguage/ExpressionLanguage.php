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
namespace Sylius\Bundle\Resource_Bundle\Expression_Language;

use Psr\Cache\Cache_Item_Pool_Interface;
use Symfony\Component\Dependency_Injection\Expression_Language as BaseExpressionLanguage;
use Symfony\Component\Expression_Language\Parser_Cache\Parser_Cache_Adapter;
use Symfony\Component\Expression_Language\Parser_Cache\Parser_Cache_Interface;
final class Expression_Language extends Base_Expression_Language
{
    /**
     * @param mixed $cache
     */
    public function __construct($cache = null, array $providers = [])
    {
        if (null !== $cache) {
            if ($cache instanceof Parser_Cache_Interface) {
                trigger_deprecation('sylius/resource-bundle', '1.2', 'Passing an instance of "%s" as the first constructor argument of "%s" is deprecated. Pass an instance of "%s" instead.', Parser_Cache_Interface::class, self::class, Cache_Item_Pool_Interface::class);
                /** @var CacheItemPoolInterface $cache */
                $cache = new Parser_Cache_Adapter($cache);
            } elseif (!$cache instanceof Cache_Item_Pool_Interface) {
                throw new \InvalidArgumentException(sprintf('Cache argument has to implement %s.', Cache_Item_Pool_Interface::class));
            }
        }
        array_unshift($providers, new Not_Null_Expression_Function_Provider());
        parent::__construct($cache, $providers);
    }
}