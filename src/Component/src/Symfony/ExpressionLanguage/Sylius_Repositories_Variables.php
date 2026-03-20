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
namespace Sylius\Resource\Symfony\Expression_Language;

use Psr\Container\Container_Interface;
final readonly class Sylius_Repositories_Variables implements Variables_Interface
{
    public function __construct(private Container_Interface $repositories)
    {
    }
    public function get_variables(): array
    {
        return ['sylius_repositories' => $this->repositories];
    }
}