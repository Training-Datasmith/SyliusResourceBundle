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
namespace Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver;

use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
interface Driver_Interface
{
    public function load(Container_Builder $container, Metadata_Interface $metadata): void;
    /**
     * Returns unique name of the driver.
     */
    public function get_type(): string;
}