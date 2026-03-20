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
namespace Sylius\Bundle\Resource_Bundle\Form\Data_Transformer;

use Doctrine\Common\Collections\Array_Collection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Data_Transformer_Interface;
use Symfony\Component\Form\Exception\Transformation_Failed_Exception;
final readonly class Collection_To_String_Transformer implements Data_Transformer_Interface
{
    public function __construct(private string $delimiter)
    {
    }
    public function transform($value): string
    {
        if (!$value instanceof Collection) {
            throw new Transformation_Failed_Exception(sprintf('Expected "%s", but got "%s"', Collection::class, get_debug_type($value)));
        }
        if ($value->is_empty()) {
            return '';
        }
        return implode($this->delimiter, $value->to_array());
    }
    public function reverse_transform($value): Collection
    {
        if (!is_string($value)) {
            throw new Transformation_Failed_Exception(sprintf('Expected string, but got "%s"', get_debug_type($value)));
        }
        if ('' === $value) {
            return new Array_Collection();
        }
        /** Explode would return string[]|false for PHP 7.4 and string[] for PHP 8 which messes in PHPStan algorithms */
        return new Array_Collection(explode($this->delimiter, $value) ?: []);
        // @phpstan-ignore-line
    }
}