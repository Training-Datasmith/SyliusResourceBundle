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
namespace Sylius\Resource\Metadata;

interface Factory_Aware_Operation_Interface
{
    public function get_factory(): callable|string|false|null;
    public function with_factory(string|callable|false|null $factory): self;
    public function get_factory_method(): ?string;
    public function with_factory_method(string $factory_method): self;
    public function get_factory_arguments(): ?array;
    public function with_factory_arguments(array $factory_arguments): self;
}