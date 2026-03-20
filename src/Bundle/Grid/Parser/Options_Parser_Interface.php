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
namespace Sylius\Bundle\Resource_Bundle\Grid\Parser;

use Symfony\Component\Http_Foundation\Request;
interface Options_Parser_Interface
{
    /**
     * @param mixed $data
     */
    public function parse_options(array $parameters, Request $request, $data = null): array;
}