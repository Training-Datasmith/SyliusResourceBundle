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
namespace Sylius\Resource\Factory;

/**
 * Creates resources based on theirs FQCN.
 */
final readonly class Factory implements Factory_Interface
{
    /**
     * @param class-string $className
     */
    public function __construct(private string $class_name)
    {
    }
    public function create_new()
    {
        return new $this->class_name();
    }
}
if (!class_exists(\Sylius\Component\Resource\Factory\Factory::class, false)) {
    class_alias(Factory::class, \Sylius\Component\Resource\Factory\Factory::class);
}