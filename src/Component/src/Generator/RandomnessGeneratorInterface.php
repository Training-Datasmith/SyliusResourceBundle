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
namespace Sylius\Resource\Generator;

interface Randomness_Generator_Interface
{
    public function generate_uri_safe_string(int $length): string;
    public function generate_numeric(int $length): string;
    public function generate_int(int $min, int $max): int;
}
if (!class_exists(\Sylius\Component\Resource\Generator\Randomness_Generator_Interface::class, false)) {
    class_alias(Randomness_Generator_Interface::class, \Sylius\Component\Resource\Generator\Randomness_Generator_Interface::class);
}