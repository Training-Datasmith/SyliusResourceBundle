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

/**
 * @experimental
 */
interface Argument_Parser_Interface
{
    public function parse_expression(string $expression, array $variables = []): mixed;
}