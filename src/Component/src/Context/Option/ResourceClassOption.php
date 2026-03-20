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
namespace Sylius\Resource\Context\Option;

final readonly class Resource_Class_Option
{
    /** @param class-string $resourceClass */
    public function __construct(private string $resource_class)
    {
    }
    /** @return class-string */
    public function resource_class(): string
    {
        return $this->resource_class;
    }
}