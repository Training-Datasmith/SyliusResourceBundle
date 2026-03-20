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

use Webmozart\Assert\Assert;
final readonly class Randomness_Generator implements Randomness_Generator_Interface
{
    private string $uri_safe_alphabet;
    private string $digits;
    public function __construct()
    {
        $this->digits = implode('', range(0, 9));
        $this->uri_safe_alphabet = implode('', range(0, 9)) . implode('', range('a', 'z')) . implode('', range('A', 'Z')) . implode('', ['-', '_', '~']);
    }
    public function generate_uri_safe_string(int $length): string
    {
        return $this->generate_string_of_length($length, $this->uri_safe_alphabet);
    }
    public function generate_numeric(int $length): string
    {
        return $this->generate_string_of_length($length, $this->digits);
    }
    public function generate_int(int $min, int $max): int
    {
        return random_int($min, $max);
    }
    private function generate_string_of_length(int $length, string $alphabet): string
    {
        $alphabet_max_index = strlen($alphabet) - 1;
        Assert::greater_than_eq($alphabet_max_index, 1);
        $random_string = '';
        for ($i = 0; $i < $length; ++$i) {
            $index = random_int(0, $alphabet_max_index);
            $random_string .= $alphabet[$index];
        }
        return $random_string;
    }
}
if (!class_exists(\Sylius\Component\Resource\Generator\Randomness_Generator::class, false)) {
    class_alias(Randomness_Generator::class, \Sylius\Component\Resource\Generator\Randomness_Generator::class);
}