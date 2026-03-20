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

/**
 * The Operation has a grid.
 *
 * @experimental
 */
interface Grid_Aware_Operation_Interface
{
    public function get_grid(): ?string;
    public function with_grid(string $grid): self;
}